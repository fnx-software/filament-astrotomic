<?php

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Tables;

use Astrotomic\Translatable\Contracts\Translatable;
use Closure;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TranslatableColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->formatStateUsing(function (Model $record, $livewire): ?string {
            $fullName = $this->getName();
            $locale = $this->resolveActiveLocale($livewire);

            if (! Str::contains($fullName, '.')) {
                if (! method_exists($record, 'getTranslation')) {
                    return $record->{$fullName};
                }

                $translation = $record->getTranslation($locale, true);

                return $translation?->{$fullName};
            }

            $relationshipPath = Str::beforeLast($fullName, '.');
            $attributeName = Str::afterLast($fullName, '.');

            $relatedRecord = data_get($record, $relationshipPath);

            if (! $relatedRecord || ! method_exists($relatedRecord, 'getTranslation')) {
                return null;
            }

            $translation = $relatedRecord->getTranslation($locale, true);

            return $translation?->{$attributeName};
        });
    }

    public function sortable(
        bool | Closure | array | string $condition = true,
        ?Closure $query = null
    ): static {
        $this->isSortable = $condition;

        if ($query === null) {
            $this->sortQuery = function (Builder $query, string $direction) {
                $fullName = $this->getName();
                $locale = $this->resolveActiveLocale($this->getLivewire());

                if (! Str::contains($fullName, '.')) {
                    return $query->orderByTranslation($fullName, $direction, $locale);
                }

                $relationshipPath = Str::beforeLast($fullName, '.');
                $attributeName = Str::afterLast($fullName, '.');

                /** @var Model $model */
                $model = $query->getModel();

                if (! (method_exists($model, $relationshipPath) && $model->{$relationshipPath}() instanceof BelongsTo)) {
                    return $query;
                }

                /** @var BelongsTo $relationship */
                $relationship = $model->{$relationshipPath}();

                /** @var Model&Translatable $relatedModel */
                $relatedModel = $relationship->getRelated();

                $relatedTable = $relatedModel->getTable();
                $ownerKey = $relationship->getOwnerKeyName();
                $foreignKey = $relationship->getForeignKeyName();

                $translationTable = $relatedModel->getTranslationsTable();
                $translationForeignKey = $relatedModel->getForeignKey();

                return $query
                    ->leftJoin($relatedTable, "{$model->getTable()}.{$foreignKey}", '=', "{$relatedTable}.{$ownerKey}")
                    ->leftJoin($translationTable, function ($join) use (
                        $relatedTable,
                        $relatedModel,
                        $translationTable,
                        $translationForeignKey,
                        $locale
                    ) {
                        $join
                            ->on(
                                "{$relatedTable}.{$relatedModel->getKeyName()}",
                                '=',
                                "{$translationTable}.{$translationForeignKey}"
                            )
                            ->where("{$translationTable}.locale", '=', $locale);
                    })
                    ->select("{$model->getTable()}.*")
                    ->orderBy("{$translationTable}.{$attributeName}", $direction)
                    ->groupBy("{$model->getTable()}.{$model->getKeyName()}");
            };
        } else {
            $this->sortQuery = $query;
        }

        return $this;
    }

    public function searchable(
        bool | array | string | Closure $condition = true,
        ?Closure $query = null,
        bool $isIndividual = false,
        bool $isGlobal = true
    ): static {
        if (is_bool($condition)) {
            $this->isSearchable = $condition;
            $this->searchColumns = null;
        } else {
            $this->isSearchable = true;
            $this->searchColumns = Arr::wrap($condition);
        }

        if ($query === null) {
            $query = function (Builder $query, string $search) {
                $fullName = $this->getName();
                $locale = $this->resolveActiveLocale($this->getLivewire());

                if (! Str::contains($fullName, '.')) {
                    return $query->whereTranslationLike($fullName, "%{$search}%", $locale);
                }

                $relationshipPath = Str::beforeLast($fullName, '.');
                $attributeName = Str::afterLast($fullName, '.');

                return $query->whereHas($relationshipPath, function (Builder $q) use ($attributeName, $search, $locale) {
                    $q->whereTranslationLike($attributeName, "%{$search}%", $locale);
                });
            };
        }

        $this->searchQuery = $query;
        $this->isGloballySearchable = $isGlobal;
        $this->isIndividuallySearchable = $isIndividual;

        return $this;
    }

    protected function resolveActiveLocale(mixed $livewire = null): string
    {
        if (
            is_object($livewire) &&
            property_exists($livewire, 'activeLocale') &&
            filled($livewire->activeLocale)
        ) {
            return $livewire->activeLocale;
        }

        return app()->getLocale();
    }
}

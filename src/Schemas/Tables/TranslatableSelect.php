<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Tables;

use Closure;
use Filament\Forms\Components\Select;
use Fnxsoftware\FilamentAstrotomic\Concerns\HasTranslatableRecordLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class TranslatableSelect extends Select
{
    use HasTranslatableRecordLabel;

    protected ?string $translatableRelationshipName = null;

    protected ?string $translatableTitleAttribute = null;

    protected ?Closure $translatableModifyQueryUsing = null;

    public function translatableRelationship(
        string $relationship,
        string $titleAttribute,
        ?Closure $modifyQueryUsing = null,
    ): static {
        $this->translatableRelationshipName = $relationship;
        $this->translatableTitleAttribute = $titleAttribute;
        $this->translatableModifyQueryUsing = $modifyQueryUsing;

        /*
         * Do not pass $titleAttribute to Filament's relationship().
         *
         * Filament's normal relationship titleAttribute must be a real column on
         * the related table. Astrotomic translated attributes such as "name",
         * "first_name", "last_name" live in the translation table, so passing
         * them to relationship() causes SQL like:
         *
         * select hr_departments.name, hr_departments.id from hr_departments
         *
         * which fails because hr_departments.name does not exist.
         */
        $this->relationship(
            name: $relationship,
            modifyQueryUsing: $modifyQueryUsing,
        );

        $this->options(fn (): array => $this->getTranslatableOptions());

        $this->getSearchResultsUsing(
            fn (string $search): array => $this->getTranslatableSearchResults($search),
        );

        $this->getOptionLabelUsing(
            fn ($value): ?string => $this->getTranslatableOptionLabel($value),
        );

        $this->getOptionLabelFromRecordUsing(
            fn (Model $record): ?string => $this->resolveTranslatableRecordLabel(
                record: $record,
                fallbackAttribute: $this->getTranslatableTitleAttribute(),
            ),
        );

        return $this;
    }

    public function getTranslatableRelationshipName(): ?string
    {
        return $this->translatableRelationshipName;
    }

    public function getTranslatableTitleAttribute(): string
    {
        return $this->translatableTitleAttribute ?? 'name';
    }

    protected function getTranslatableOptions(): array
    {
        $query = $this->getTranslatableRelatedQuery();

        if (! $query) {
            return [];
        }

        return $query
            ->limit($this->getOptionsLimit())
            ->get()
            ->mapWithKeys(fn (Model $record): array => [
                $record->getKey() => $this->resolveTranslatableRecordLabel(
                    record: $record,
                    fallbackAttribute: $this->getTranslatableTitleAttribute(),
                ),
            ])
            ->all();
    }

    protected function getTranslatableSearchResults(string $search): array
    {
        $query = $this->getTranslatableRelatedQuery();

        if (! $query) {
            return [];
        }

        $this->applyTranslatableSearchAttributes(
            query: $query,
            search: $search,
            fallbackAttribute: $this->getTranslatableTitleAttribute(),
        );

        return $query
            ->limit($this->getOptionsLimit())
            ->get()
            ->mapWithKeys(fn (Model $record): array => [
                $record->getKey() => $this->resolveTranslatableRecordLabel(
                    record: $record,
                    fallbackAttribute: $this->getTranslatableTitleAttribute(),
                ),
            ])
            ->all();
    }

    protected function getTranslatableOptionLabel(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $query = $this->getTranslatableRelatedQuery();

        if (! $query) {
            return null;
        }

        $record = $query->find($value);

        return $this->resolveTranslatableRecordLabel(
            record: $record,
            fallbackAttribute: $this->getTranslatableTitleAttribute(),
        );
    }

    protected function getTranslatableRelatedQuery(): ?Builder
    {
        $relationship = $this->getRelationship();

        if (! $relationship instanceof Relation) {
            return null;
        }

        $query = $relationship
            ->getRelated()
            ->newQuery()
            ->with('translations');

        if ($this->translatableModifyQueryUsing instanceof Closure) {
            $query = $this->evaluate($this->translatableModifyQueryUsing, [
                'query' => $query,
            ]) ?? $query;
        }

        return $query;
    }
}

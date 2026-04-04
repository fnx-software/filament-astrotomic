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

// Add this use statement for Arr::wrap

class TranslatableColumn extends TextColumn
{
    /**
     * This method is executed when the class is initialized.
     * We'll set up our default behaviors here.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set the default display logic for the column's state.
        $this->formatStateUsing(function (Model $record, $livewire): ?string {
            $fullName = $this->getName(); // This will be 'country.name' or just 'name'

            // Determine the locale to use from the LocaleSwitcher, with a fallback.
            $locale = property_exists($livewire, 'activeLocale') && $livewire->activeLocale
                ? $livewire->activeLocale
                : app()->getLocale();

            // Check if we are dealing with a nested relationship
            if (! Str::contains($fullName, '.')) {
                // --- HANDLE DIRECT ATTRIBUTES ---
                if (! method_exists($record, 'getTranslation')) {
                    return $record->{$fullName};
                }

                return $record->getTranslation($locale, true)[$fullName];
            }

            // --- HANDLE NESTED RELATIONSHIPS ---
            $relationshipPath = Str::beforeLast($fullName, '.'); // e.g., 'country'
            $attributeName = Str::afterLast($fullName, '.');    // e.g., 'name'

            // Safely retrieve the related model using the path.
            $relatedRecord = data_get($record, $relationshipPath);

            // Check if the related record exists and is a translatable model.
            if (! $relatedRecord || ! method_exists($relatedRecord, 'getTranslation')) {
                return null;
            }

            // Return the translation from the related model.
            return $relatedRecord->getTranslation($locale, true)[$attributeName];
        });
    }

    /**
     * Override the sortable() method to provide our own
     * default sort logic for translatable fields.
     */
    public function sortable(
        bool | Closure | array | string $condition = true,
        ?Closure $query = null
    ): static {
        // Replicate the parent's logic to set the sortable condition
        $this->isSortable = $condition;

        // If a custom query is not provided, create our own.
        if ($query === null) {
            $this->sortQuery = function (Builder $query, string $direction) {
                $fullName = $this->getName();

                // --- HANDLE DIRECT ATTRIBUTES ---
                if (! Str::contains($fullName, '.')) {
                    return $query->orderByTranslation($fullName, $direction);
                }

                // --- HANDLE NESTED BelongsTo RELATIONSHIPS ---
                $relationshipPath = Str::beforeLast($fullName, '.');
                $attributeName = Str::afterLast($fullName, '.');

                /** @var Model $model */
                $model = $query->getModel();

                // This implementation supports single-level BelongsTo relationships.
                if (! (method_exists($model, $relationshipPath) && $model->{$relationshipPath}() instanceof BelongsTo)) {
                    return $query; // Silently fail if not a supported relationship
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

                $query
                    ->leftJoin($relatedTable, "{$model->getTable()}.{$foreignKey}", '=', "{$relatedTable}.{$ownerKey}")
                    ->leftJoin($translationTable, "{$relatedTable}.{$relatedModel->getKeyName()}", '=', "{$translationTable}.{$translationForeignKey}")
                    ->where("{$translationTable}.locale", app()->getLocale())
                    ->select("{$model->getTable()}.*") // Crucial to avoid pulling in conflicting columns
                    ->orderBy("{$translationTable}.{$attributeName}", $direction)
                    ->groupBy("{$model->getTable()}.{$model->getKeyName()}"); // Ensure unique results
            };
        } else {
            // If the user provided their own query, use it.
            $this->sortQuery = $query;
        }

        return $this;
    }

    /**
     * We override the searchable() method to provide our own
     * default search logic for translatable fields.
     */
    public function searchable(
        // **MODIFICATION HERE: Update the type hint for $condition**
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

        // If the developer has not provided a custom search query...
        if ($query === null) {
            // ...we will define our own default query.
            $query = function (Builder $query, string $search) {
                $fullName = $this->getName();

                // --- NEW SEARCH LOGIC ---

                // If it's not a relationship, use the simple search.
                if (! Str::contains($fullName, '.')) {
                    return $query->whereTranslationLike($fullName, "%{$search}%");
                }

                // If it IS a relationship, use a `whereHas` query.
                $relationshipPath = Str::beforeLast($fullName, '.');
                $attributeName = Str::afterLast($fullName, '.');

                return $query->whereHas($relationshipPath, function (Builder $q) use ($attributeName, $search) {
                    $q->whereTranslationLike($attributeName, "%{$search}%");
                });
            };
        }

        $this->searchQuery = $query;
        $this->isGloballySearchable = $isGlobal;
        $this->isIndividuallySearchable = $isIndividual;

        return $this;
    }
}

<?php

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Tables;

use Closure;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr; // Add this use statement for Arr::wrap

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
            $columnName = $this->getName();

            $locale = property_exists($livewire, 'activeLocale') && $livewire->activeLocale
                ? $livewire->activeLocale
                : app()->getLocale();

            if (! method_exists($record, 'getTranslation')) {
                return $record->{$columnName};
            }

            return $record->getTranslation($locale, true)[$columnName];
        });
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
        // **MODIFICATION HERE: Replicate parent's logic for $condition**
        if (is_bool($condition)) {
            $this->isSearchable = $condition;
            $this->searchColumns = null;
        } else {
            $this->isSearchable = true;
            $this->searchColumns = Arr::wrap($condition);
        }

        // If the developer has not provided a custom search query for the translation...
        if ($query === null) {
            // ...we will define our own default query.
            $query = function (Builder $query, string $search) {
                $columnName = $this->getName();

                return $query->whereTranslationLike($columnName, "%{$search}%");
            };
        }

        // Apply our custom query or the one provided by the user
        $this->searchQuery = $query;
        $this->isGloballySearchable = $isGlobal;
        $this->isIndividuallySearchable = $isIndividual;

        // **Important**: Do not call parent::searchable() directly anymore after replicating its logic.
        // Instead, return $this to maintain method chaining, as the parent's logic is already
        // applied by setting the internal properties.
        return $this;
    }
}

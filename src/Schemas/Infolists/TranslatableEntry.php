<?php

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Infolists;

use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranslatableEntry extends TextEntry
{
    /**
     * This method is executed when the class is initialized.
     * We'll set up our default display logic here.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set the default display logic for the component's state.
        $this->formatStateUsing(function (Model $record, $livewire): ?string {
            $fullName = $this->getName(); // This will be 'country.name' or just 'name'

            // Determine the locale to use from the LocaleSwitcher, with a fallback.
            $locale = property_exists($livewire, 'activeLocale') && $livewire->activeLocale
                ? $livewire->activeLocale
                : app()->getLocale();

            // --- NEW LOGIC STARTS HERE ---

            // Check if we are dealing with a nested relationship
            if (! Str::contains($fullName, '.')) {
                // --- HANDLE DIRECT ATTRIBUTES (Original Logic) ---
                if (! method_exists($record, 'getTranslation')) {
                    return $record->{$fullName};
                }

                // The `false` parameter prevents fallback to the default locale
                return $record->getTranslation($locale, true)[$fullName];
            }

            // --- HANDLE NESTED RELATIONSHIPS ---
            $relationshipPath = Str::beforeLast($fullName, '.'); // e.g., 'country'
            $attributeName = Str::afterLast($fullName, '.');    // e.g., 'name'

            // Safely retrieve the related model using the path.
            // `data_get` is perfect for this, as it handles nulls gracefully.
            $relatedRecord = data_get($record, $relationshipPath);

            // Check if the related record exists and is a translatable model.
            if (! $relatedRecord || ! method_exists($relatedRecord, 'getTranslation')) {
                // If the relation doesn't exist or isn't translatable, return null
                // to avoid errors and display an empty value.
                return null;
            }

            // Return the translation from the related model.
            return $relatedRecord->getTranslation($locale, true)[$attributeName];
        });
    }
}

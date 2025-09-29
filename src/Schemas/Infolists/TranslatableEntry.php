<?php

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Infolists;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;

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
            // Get the name of the entry component (e.g., 'name', 'title').
            $entryName = $this->getName();

            // Check if the parent Livewire component (the View page) has an 'activeLocale' property,
            // which is set by the LocaleSwitcher. Fallback to the app's current locale.
            $locale = property_exists($livewire, 'activeLocale') && $livewire->activeLocale
                ? $livewire->activeLocale
                : app()->getLocale();

            // Ensure the model has the getTranslation method from the astrotomic package.
            if (! method_exists($record, 'getTranslation')) {
                // If not, fall back to the default behavior of displaying the attribute.
                return $record->{$entryName};
            }

            // Return the specific translation for the active locale.
            // The `false` parameter prevents it from falling back to the default locale
            // if the requested translation does not exist.
            return $record->getTranslation($locale, true)[$entryName];
        });
    }
}

<?php

namespace Fnxsoftware\FilamentAstrotomic\Actions\Concerns;

use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;

trait HasTranslatableLocaleOptions
{
    public function setTranslatableLocaleOptions(): static
    {
        $this->options(function (): array {
            $livewire = $this->getLivewire();

            if (! method_exists($livewire, 'getTranslatableLocales')) {
                return [];
            }

            $locales = [];

            /** @var FilamentAstrotomicPlugin $plugin */
            $plugin = filament('filament-astrotomic');

            foreach ($livewire->getTranslatableLocales() as $locale) {
                $locales[$locale] = $plugin->getLocaleLabel($locale) ?? $locale;
            }

            return $locales;
        });

        return $this;
    }
}

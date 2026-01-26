<?php

namespace Fnxsoftware\FilamentAstrotomic\Actions\Concerns;

use Closure;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;

trait HasTranslatableLocaleOptions
{
    protected array | Closure | null $customLocales = null;

    /**
     * Set custom locales for this specific action instance.
     */
    public function locales(array | Closure | null $locales): static
    {
        $this->customLocales = $locales;

        return $this;
    }

    public function setTranslatableLocaleOptions(): static
    {
        $this->options(function (): array {
            /** @var FilamentAstrotomicPlugin $plugin */
            $plugin = filament('filament-astrotomic');
            $livewire = $this->getLivewire();

            $rawLocales = [];

            // 1. Priority: Custom locales defined on the Action itself
            if ($this->customLocales !== null) {
                $rawLocales = $this->evaluate($this->customLocales);
            }
        
            // 3. Priority: Fallback to global Plugin locales (handles Tenant logic)
            // We check this if $rawLocales is still empty
            if (empty($rawLocales)) {

                $rawLocales = $plugin->getLocales();
            }

            // 4. Format the options for the Select
            $options = [];

            foreach ($rawLocales as $key => $value) {
                // If the user provided ['en' => 'English Custom Label']
                if (is_string($key) && ! is_numeric($key)) {
                    $options[$key] = $value;

                    continue;
                }

                // If the user provided ['en', 'fr'], generate labels using the plugin
                $localeCode = $value;
                $options[$localeCode] = $plugin->getLocaleLabel($localeCode) ?? $localeCode;
            }

            return $options;
        });

        return $this;
    }
}

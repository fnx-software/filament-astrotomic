<?php

namespace Fnxsoftware\FilamentAstrotomic;

use Astrotomic\Translatable\Locales;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Str;

class FilamentAstrotomicPlugin implements Plugin
{
    protected ?Closure $getLocaleLabelUsing = null;

    // 1. Add a property to hold the custom main locale (string or Closure)
    protected string | Closure | null $mainLocale = null;

    public function getId(): string
    {
        return 'filament-astrotomic';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /**
     * Package specific functions
     */

    // 2. Add the public "setter" method to allow configuration
    public function mainLocale(string | Closure $locale): static
    {
        $this->mainLocale = $locale;

        return $this;
    }

    public function allLocales(): array
    {
        return app(Locales::class)->all();
    }

    public function getMainLocale(): string
    {
        // 3. Update getMainLocale() to use the custom property if it exists
        if (isset($this->mainLocale)) {
            // Evaluate the property, which executes the Closure if it is one,
            // or returns the value if it's a simple string.
            $locale = $this->mainLocale;

            return $locale instanceof Closure ? $locale() : $locale;
        }

        // Fallback to the original behavior (get from config) if not set
        return app(Locales::class)->current();
    }

    public function getLocaleLabelUsing(?Closure $callback): static
    {
        $this->getLocaleLabelUsing = $callback;

        return $this;
    }

    public function getLocaleLabel(string $locale, ?string $displayLocale = null): ?string
    {
        $displayLocale ??= app()->getLocale();

        $label = null;

        if ($callback = $this->getLocaleLabelUsing) {
            $label = $callback($locale, $displayLocale);
        }

        return $label ?? Str::ucfirst(locale_get_display_name($locale, $displayLocale) ?: '');
    }
}

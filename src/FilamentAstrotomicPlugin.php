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

    protected string | Closure | null $mainLocale = null;

    protected array | Closure | null $locales = null;

    /**
     * If true, translatable components will always render tabs/switchers
     * even if there is only one locale.
     */
    protected bool | Closure $isForced = false;

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
    public function mainLocale(string | Closure $locale): static
    {
        $this->mainLocale = $locale;

        return $this;
    }

    public function locales(array | Closure $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Force the display of translatable UI (tabs, switchers)
     * even if there is only one locale available.
     */
    public function force(bool | Closure $condition = true): static
    {
        $this->isForced = $condition;

        return $this;
    }

    public function isForced(): bool
    {
        return (bool) ($this->isForced instanceof Closure ? ($this->isForced)() : $this->isForced);
    }

    public function allLocales(): array
    {
        return app(Locales::class)->all();
    }

    public function getLocales(): array
    {
        if ($this->locales === null) {
            return app(Locales::class)->all();
        }

        $value = $this->locales instanceof Closure ? ($this->locales)() : $this->locales;

        $list = $this->normalizeLocales($value);

        $main = $this->getMainLocale();

        return array_values(array_unique(array_merge([$main], $list)));
    }

    protected function normalizeLocales(mixed $value): array
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    public function getMainLocale(): string
    {
        if (isset($this->mainLocale)) {
            $locale = $this->mainLocale;

            return $locale instanceof Closure ? $locale() : $locale;
        }

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

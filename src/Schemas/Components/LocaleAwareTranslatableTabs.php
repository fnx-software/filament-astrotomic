<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Components;

use Closure;
use Filament\Actions\Action;

class LocaleAwareTranslatableTabs extends TranslatableTabs
{
    /** @var array<int, string> | Closure */
    protected array | Closure $displayCustomLocales = [];

    /**
     * Define which custom locales are visible initially when a locale picker is enabled.
     *
     * @param  array<int, string> | Closure  $locales
     */
    public function displayCustomLocales(array | Closure $locales): static
    {
        $this->displayCustomLocales = $locales;
        $this->cachedVisibleLocales = null;

        return $this;
    }

    /**
     * Register the suffix picker as a prepared action immediately.
     * This avoids deferred closure registration issues in nested schema tabs.
     */
    public function hasSuffixLocalePicker(bool | Closure $condition = true): static
    {
        $this->hasSuffixLocalePicker = $condition;

        $this->suffixAction(
            $this->makeLocalePickerAction()
                ->visible(fn (): bool => $this->isSuffixLocalePickerEnabled()),
        );

        return $this;
    }

    /**
     * Register the prefix picker as a prepared action immediately.
     */
    public function hasPrefixLocalePicker(bool | Closure $condition = true): static
    {
        $this->hasPrefixLocalePicker = $condition;

        $this->prefixAction(
            $this->makeLocalePickerAction()
                ->visible(fn (): bool => $this->isPrefixLocalePickerEnabled()),
        );

        return $this;
    }

    protected function getVisibleLocales(): array
    {
        if (is_array($this->cachedVisibleLocales)) {
            return $this->cachedVisibleLocales;
        }

        if ($this->isResolvingVisibleLocales) {
            return [$this->mainLocale];
        }

        $this->isResolvingVisibleLocales = true;

        try {
            $allowedLocales = array_keys($this->getConfiguredLocaleOptions());

            if (! $this->hasLocalePicker()) {
                return $this->cachedVisibleLocales = $allowedLocales;
            }

            $storedLocales = session()->get($this->getVisibleLocalesStorePath());

            if (is_array($storedLocales) && $storedLocales !== []) {
                return $this->cachedVisibleLocales = $this->normalizeLocaleCodes(
                    $storedLocales,
                    $allowedLocales,
                );
            }

            $initialLocales = $this->normalizeLocaleCodes(
                (array) $this->evaluate($this->displayCustomLocales),
                $allowedLocales,
            );

            return $this->cachedVisibleLocales = $this->normalizeLocaleCodes([
                $this->mainLocale,
                ...$initialLocales,
                ...$this->detectExistingLocales(),
            ], $allowedLocales);
        } finally {
            $this->isResolvingVisibleLocales = false;
        }
    }

    protected function hasLocalePicker(): bool
    {
        return $this->isPrefixLocalePickerEnabled()
            || $this->isSuffixLocalePickerEnabled();
    }
}

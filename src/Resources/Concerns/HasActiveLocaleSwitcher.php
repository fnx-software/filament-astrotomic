<?php

namespace Fnxsoftware\FilamentAstrotomic\Resources\Concerns;

trait HasActiveLocaleSwitcher
{
    public ?string $activeLocale = null;

    public function getActiveFormsLocale(): ?string
    {
        if (! in_array($this->activeLocale, $this->getTranslatableLocales())) {
            return null;
        }

        return $this->activeLocale;
    }

    public function mountHasActiveLocaleSwitcher(): void
    {
        $this->activeLocale = app()->getLocale();
    }

    public function updatedActiveLocale(string $newActiveLocale): void
    {
        $this->activeLocale = $newActiveLocale;

    }

    public function getActiveActionsLocale(): ?string
    {
        return $this->activeLocale;
    }
}

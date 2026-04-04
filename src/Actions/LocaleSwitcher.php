<?php

namespace Fnxsoftware\FilamentAstrotomic\Actions;

use Filament\Actions\SelectAction;

class LocaleSwitcher extends SelectAction
{
    use Concerns\HasTranslatableLocaleOptions;

    public static function getDefaultName(): ?string
    {
        return 'activeLocale';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-astrotomic::actions.active_locale.label'));

        $this->setTranslatableLocaleOptions();

        // HIDE if only 1 locale is available
        $this->hidden(function () {
            return count($this->getOptions()) <= 1;
        });
    }
}

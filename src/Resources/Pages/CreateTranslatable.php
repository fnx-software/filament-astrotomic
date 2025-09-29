<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasActiveLocaleSwitcher;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasLocales;

/**
 * @mixin CreateRecord
 */
trait CreateTranslatable
{
    use HasActiveLocaleSwitcher;
    use HasLocales;

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }
}

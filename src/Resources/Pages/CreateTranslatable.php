<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasLocales;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasActiveLocaleSwitcher;
/**
 * @mixin CreateRecord
 */
trait CreateTranslatable
{
    use HasLocales;
    use HasActiveLocaleSwitcher;

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }
}

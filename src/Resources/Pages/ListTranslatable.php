<?php

namespace Fnxsoftware\FilamentAstrotomic\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasActiveLocaleSwitcher;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasLocales;

/**
 * @mixin ListRecords
 */
trait ListTranslatable
{
    use HasActiveLocaleSwitcher;
    use HasLocales;
}

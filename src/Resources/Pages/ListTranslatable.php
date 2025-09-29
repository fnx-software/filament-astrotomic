<?php

namespace Fnxsoftware\FilamentAstrotomic\Resources\Pages;

use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasLocales;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasActiveLocaleSwitcher;
use Filament\Resources\Pages\ListRecords;

/**
 * @mixin ListRecords
 */
trait ListTranslatable
{

    use HasLocales;
    use HasActiveLocaleSwitcher;

}

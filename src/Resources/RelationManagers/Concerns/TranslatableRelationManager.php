<?php

namespace Fnxsoftware\FilamentAstrotomic\Resources\RelationManagers\Concerns;

use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasLocales;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasActiveLocaleSwitcher;

trait TranslatableRelationManager
{
    use HasLocales;
    use HasActiveLocaleSwitcher;
}

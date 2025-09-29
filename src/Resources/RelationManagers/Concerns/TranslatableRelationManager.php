<?php

namespace Fnxsoftware\FilamentAstrotomic\Resources\RelationManagers\Concerns;

use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasActiveLocaleSwitcher;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasLocales;

trait TranslatableRelationManager
{
    use HasActiveLocaleSwitcher;
    use HasLocales;
}

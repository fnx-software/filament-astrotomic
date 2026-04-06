<?php

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Components;

use Filament\Schemas\Components\Contracts\HasAffixActions;
use Filament\Schemas\Components\Tabs;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\Concerns\HasHeaderActions;

class AdvancedTabs extends Tabs implements HasAffixActions
{
    use HasHeaderActions;

    protected string $view = 'filament-astrotomic::schemas.components.advanced-tabs';
}

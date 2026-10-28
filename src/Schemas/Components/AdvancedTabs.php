<?php

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Components;

use Filament\Schemas\Components\Contracts\HasAffixActions;
use Filament\Schemas\Components\Tabs;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\Concerns\HasHeaderActions;

class AdvancedTabs extends Tabs implements HasAffixActions
{
    use HasHeaderActions;

    // The inherited Filament Tabs view correctly renders ordinary locale fields.
    // Custom header actions opt into the advanced view when needed.
}

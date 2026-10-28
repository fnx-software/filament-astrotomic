<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Components;

use Filament\Actions\Action;
use Filament\Schemas\Components\Contracts\HasAffixActions;
use Filament\Schemas\Components\Tabs;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\Concerns\HasHeaderActions;

class AdvancedTabs extends Tabs implements HasAffixActions
{
    use HasHeaderActions;

    public function toEmbeddedHtml(): string
    {
        $prefixActions = array_filter(
            $this->getPrefixActions(),
            static fn (Action $action): bool => $action->isVisible(),
        );
        $suffixActions = array_filter(
            $this->getSuffixActions(),
            static fn (Action $action): bool => $action->isVisible(),
        );

        if ((! $prefixActions) && (! $suffixActions)) {
            return parent::toEmbeddedHtml();
        }

        $prefixHtml = implode('', array_map(
            static fn (Action $action): string => $action->toHtml(),
            $prefixActions,
        ));
        $suffixHtml = implode('', array_map(
            static fn (Action $action): string => $action->toHtml(),
            $suffixActions,
        ));

        // Keep Filament's own tabs renderer so every Tab renders its child
        // schema correctly. The affix actions form a toolbar above the tabs.
        // Do not switch back to the pre-Filament-5 Blade tabs template.
        return '<div class="fi-astrotomic-tabs">'
            . '<div class="fi-astrotomic-tabs-actions flex items-center justify-between gap-2 pb-2">'
            . '<div class="flex items-center gap-2">' . $prefixHtml . '</div>'
            . '<div class="ms-auto flex items-center gap-2">' . $suffixHtml . '</div>'
            . '</div>'
            . parent::toEmbeddedHtml()
            . '</div>';
    }
}

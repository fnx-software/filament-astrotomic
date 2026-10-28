<?php

declare(strict_types=1);

use Filament\Actions\Action;
use Filament\Schemas\Components\Tabs;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\AdvancedTabs;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;

it('defaults to the Filament 5 embedded tabs renderer', function (): void {
    $tabs = AdvancedTabs::make('Translations');

    expect($tabs)->toBeInstanceOf(Tabs::class)
        ->and($tabs->hasView())->toBeFalse();
});

it('retains the embedded renderer when adding prefix and suffix actions', function (): void {
    $tabs = AdvancedTabs::make('Translations')
        ->prefixAction(Action::make('add_locale')->label('Add locale'))
        ->suffixAction(Action::make('manage_locale')->label('Manage locale'));

    expect($tabs->hasView())->toBeFalse();
});

it('never switches translated tab forms to unpublished Filament 5 Blade views', function (): void {
    $tabs = file_get_contents(__DIR__.'/../../src/Schemas/Components/TranslatableTabs.php');
    $actions = file_get_contents(__DIR__.'/../../src/Schemas/Components/Concerns/HasHeaderActions.php');

    expect($tabs)->not->toContain("->view('filament-schemas::components.grid')")
        ->and($actions)->not->toContain("->view('filament-astrotomic::schemas.components.advanced-tabs')")
        ->and(is_subclass_of(TranslatableTabs::class, AdvancedTabs::class))->toBeTrue();
});

<?php

declare(strict_types=1);

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs\Tab;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\LocaleAwareTranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;

it('renders all configured translation tabs even when translations do not exist', function (): void {
    $tabs = LocaleAwareTranslatableTabs::make('translations')
        ->localeTabSchema(fn (TranslatableTab $tab): array => [
            TextInput::make($tab->makeName('title'))->required($tab->isMainLocale()),
        ]);

    expect($tabs->getVisibleLocales())->toBe(['ar', 'en', 'fr']);
    expect($tabs->getDefaultChildComponents())->toHaveCount(3);
});

it('can hide French without removing the required main language', function (): void {
    $tabs = LocaleAwareTranslatableTabs::make('translations')
        ->hideLocales(['fr', 'ar'])
        ->localeTabSchema(fn (TranslatableTab $tab): array => [
            TextInput::make($tab->makeName('title')),
        ]);

    expect($tabs->getVisibleLocales())->toBe(['ar', 'en']);
    expect($tabs->getDefaultChildComponents())->toHaveCount(2);
});

it('accepts additional tabs and custom naming', function (): void {
    $tabs = LocaleAwareTranslatableTabs::make('translations')
        ->visibleLocales(['ar', 'en'])
        ->makeNameUsingPlainSyntax()
        ->prependTabs([
            Tab::make('general')->schema([TextInput::make('reference')]),
        ])
        ->appendTabs([
            Tab::make('metadata')->schema([TextInput::make('metadata')]),
        ])
        ->localeTabSchema(fn (TranslatableTab $tab): array => [
            TextInput::make($tab->makeName('title')),
        ]);

    expect($tabs->getVisibleLocales())->toBe(['ar', 'en']);
    expect($tabs->getDefaultChildComponents())->toHaveCount(4);
});

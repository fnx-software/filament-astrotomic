<?php

declare(strict_types=1);

use Astrotomic\Translatable\Locales;
use Fnxsoftware\FilamentAstrotomic\Tests\Fixtures\TranslationTabsForm;
use Livewire\Livewire;

it('renders the main translation field inside the Filament 5 native tab panel', function (): void {
    Livewire::test(TranslationTabsForm::class)
        ->assertOk()
        ->assertSee('Translations')
        ->assertSee('Name')
        ->assertSeeHtml('fi-sc-tabs')
        ->set('data.ar.name', 'الاسم بالعربية')
        ->set('data.en.name', 'English name')
        ->assertSet('data.ar.name', 'الاسم بالعربية')
        ->assertSet('data.en.name', 'English name');
});

it('loads configured Astrotomic locales in the standalone package test environment', function (): void {
    expect(config('translatable.locales'))->toBe(['ar', 'en', 'fr'])
        ->and(app(Locales::class)->all())->toBe(['ar', 'en', 'fr']);
});

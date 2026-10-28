<?php

declare(strict_types=1);

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

<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Tests\Fixtures;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TranslationTabsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TranslatableTabs::make('Translations')
                    ->customLocales(['ar', 'en'])
                    ->force()
                    ->localeTabSchema(fn (TranslatableTab $tab): array => [
                        TextInput::make($tab->makeName('name'))
                            ->label('Name')
                            ->required($tab->isMainLocale()),
                    ]),
            ])
            ->statePath('data');
    }

    public function render(): View
    {
        return view('filament-astrotomic-tests::translation-tabs-form');
    }
}

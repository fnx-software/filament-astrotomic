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

    public ?int $postId = null;

    public ?int $lastSavedPostId = null;

    public function mount(?int $postId = null): void
    {
        $this->postId = $postId;

        if ($postId === null) {
            $this->form->fill();

            return;
        }

        $post = TranslatedPost::query()->with('translations')->findOrFail($postId);

        $attributes = ['slug' => $post->slug];

        foreach ($post->translations as $translation) {
            $attributes[$translation->locale] = ['name' => $translation->name];
        }

        $this->form->fill($attributes);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required(),
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

    public function save(): void
    {
        $state = $this->form->getState();

        $post = $this->postId === null
            ? new TranslatedPost
            : TranslatedPost::query()->findOrFail($this->postId);

        $post->fill($state);
        $post->save();

        $this->postId = $post->getKey();
        $this->lastSavedPostId = $post->getKey();
    }

    public function render(): View
    {
        return view('filament-astrotomic-tests::translation-tabs-form');
    }
}

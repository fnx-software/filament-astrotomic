<?php

declare(strict_types=1);

use Fnxsoftware\FilamentAstrotomic\Tests\Fixtures\TranslatedPost;
use Fnxsoftware\FilamentAstrotomic\Tests\Fixtures\TranslationTabsForm;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    DB::purge('sqlite');

    Schema::create('test_translatable_posts', function (Blueprint $table): void {
        $table->id();
        $table->string('slug')->unique();
        $table->timestamps();
    });

    Schema::create('test_translatable_post_translations', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('translated_post_id')->constrained('test_translatable_posts')->cascadeOnDelete();
        $table->string('locale', 10);
        $table->string('name');
        $table->timestamps();

        $table->unique(['translated_post_id', 'locale']);
    });
});

it('creates real Astrotomic translations in SQLite from the Filament Livewire form', function (): void {
    Livewire::test(TranslationTabsForm::class)
        ->set('data.slug', 'wash')
        ->set('data.ar.name', 'المياه والصرف الصحي')
        ->set('data.en.name', 'Water, Sanitation and Hygiene')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('lastSavedPostId', 1);

    $this->assertDatabaseHas('test_translatable_posts', ['id' => 1, 'slug' => 'wash']);
    $this->assertDatabaseHas('test_translatable_post_translations', [
        'translated_post_id' => 1,
        'locale' => 'ar',
        'name' => 'المياه والصرف الصحي',
    ]);
    $this->assertDatabaseHas('test_translatable_post_translations', [
        'translated_post_id' => 1,
        'locale' => 'en',
        'name' => 'Water, Sanitation and Hygiene',
    ]);

    expect(TranslatedPost::query()->findOrFail(1)->translations()->count())->toBe(2);
});

it('loads an existing record and edits Arabic without modifying English', function (): void {
    $post = TranslatedPost::query()->create([
        'slug' => 'education',
        'ar' => ['name' => 'التعليم'],
        'en' => ['name' => 'Education'],
    ]);

    Livewire::test(TranslationTabsForm::class, ['postId' => $post->getKey()])
        ->assertSet('data.ar.name', 'التعليم')
        ->assertSet('data.en.name', 'Education')
        ->set('data.ar.name', 'التعليم والتدريب')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('test_translatable_post_translations', [
        'translated_post_id' => $post->getKey(),
        'locale' => 'ar',
        'name' => 'التعليم والتدريب',
    ]);
    $this->assertDatabaseHas('test_translatable_post_translations', [
        'translated_post_id' => $post->getKey(),
        'locale' => 'en',
        'name' => 'Education',
    ]);

    expect($post->fresh()->translations()->count())->toBe(2);
});

it('does not delete a third-language translation when only Arabic and English tabs are configured', function (): void {
    $post = TranslatedPost::query()->create([
        'slug' => 'health',
        'ar' => ['name' => 'الصحة'],
        'en' => ['name' => 'Health'],
        'fr' => ['name' => 'Santé'],
    ]);

    Livewire::test(TranslationTabsForm::class, ['postId' => $post->getKey()])
        ->set('data.en.name', 'Public Health')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('test_translatable_post_translations', [
        'translated_post_id' => $post->getKey(),
        'locale' => 'fr',
        'name' => 'Santé',
    ]);

    expect($post->fresh()->translations()->count())->toBe(3);
});

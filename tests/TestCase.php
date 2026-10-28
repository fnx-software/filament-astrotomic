<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Tests;

use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Panel;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Livewire / Filament encrypt form state during rendering. This fixed
        // test-only key is never used by consumer applications or production.
        config()->set('app.key', 'base64:' . base64_encode(str_repeat('t', 32)));
        config()->set('app.cipher', 'AES-256-CBC');

        // Astrotomic reads its own Laravel configuration, separately from
        // the locales exposed by the Filament panel plugin.
        config()->set('translatable.locales', ['ar', 'en', 'fr']);
        config()->set('translatable.locale', 'ar');

        $this->app['view']->addNamespace('filament-astrotomic-tests', __DIR__ . '/views');

        Filament::setCurrentPanel(
            Panel::make()
                ->id('filament-astrotomic-testing')
                ->plugin(FilamentAstrotomicPlugin::make()
                    ->mainLocale('ar')
                    ->locales(['ar', 'en', 'fr'])),
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            SchemasServiceProvider::class,
            FormsServiceProvider::class,
            ActionsServiceProvider::class,
            TablesServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentAstrotomicServiceProvider::class,
        ];
    }
}

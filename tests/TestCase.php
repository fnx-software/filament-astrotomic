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

        $this->app['view']->addNamespace('filament-astrotomic-tests', __DIR__.'/views');

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

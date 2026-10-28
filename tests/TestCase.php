<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Tests;

use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Panel;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
        return [FilamentServiceProvider::class, FilamentAstrotomicServiceProvider::class];
    }
}

<?php

namespace Fnxsoftware\FilamentAstrotomic;

use Fnxsoftware\FilamentAstrotomic\Commands\FilamentAstrotomicCommand;
use Fnxsoftware\FilamentAstrotomic\Testing\TestsFilamentAstrotomic;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentAstrotomicServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-astrotomic';

    public static string $viewNamespace = 'filament-astrotomic';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('fnx-software/filament-astrotomic');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-astrotomic');

        if (app()->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/filament-astrotomic'),
            ], 'filament-astrotomic-translations');

            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-astrotomic/{$file->getFilename()}"),
                ], 'filament-astrotomic-stubs');
            }
        }

        Testable::mixin(new TestsFilamentAstrotomic);
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentAstrotomicCommand::class,
        ];
    }
}

<?php

namespace Fnxsoftware\FilamentAstrotomic\Resources\Concerns;

use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicContentDriver;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;
use Filament\Support\Contracts\TranslatableContentDriver;

trait HasLocales
{
    use MutateTranslatableData;

    /**
     * @return class-string<TranslatableContentDriver> | null
     */
    public function getFilamentTranslatableContentDriver(): ?string
    {
        return FilamentAstrotomicContentDriver::class;
    }

    public static function getTranslatableLocales(): array
    {
        /** @var FilamentAstrotomicPlugin $plugin */
        $plugin = filament('filament-astrotomic');

        return $plugin->allLocales();
    }
}

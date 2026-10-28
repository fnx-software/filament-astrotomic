<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Tests;

use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [FilamentAstrotomicServiceProvider::class];
    }
}

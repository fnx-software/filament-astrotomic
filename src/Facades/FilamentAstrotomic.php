<?php

namespace Fnxsoftware\FilamentAstrotomic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Fnxsoftware\FilamentAstrotomic\FilamentAstrotomic
 */
class FilamentAstrotomic extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Fnxsoftware\FilamentAstrotomic\FilamentAstrotomic::class;
    }
}

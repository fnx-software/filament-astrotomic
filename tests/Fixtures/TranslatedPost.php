<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Tests\Fixtures;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class TranslatedPost extends Model implements TranslatableContract
{
    use Translatable;

    public array $translatedAttributes = ['name'];

    protected $table = 'test_translatable_posts';

    protected $fillable = ['slug'];
}

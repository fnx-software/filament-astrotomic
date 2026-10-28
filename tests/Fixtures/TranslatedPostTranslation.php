<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TranslatedPostTranslation extends Model
{
    protected $table = 'test_translatable_post_translations';

    protected $fillable = ['name'];
}

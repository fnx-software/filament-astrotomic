<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Resources\Pages;

use Astrotomic\Translatable\Contracts\Translatable;
use Filament\Resources\Pages\ViewRecord;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasActiveLocaleSwitcher;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasLocales;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin ViewRecord
 */
trait ViewTranslatable
{
    use HasActiveLocaleSwitcher;
    use HasLocales;

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        /** @var Model|Translatable $record */
        $record = $this->getRecord();

        $recordAttributes = $record->attributesToArray();

        $recordAttributes = $this->mutateTranslatableData($record, $recordAttributes);

        $recordAttributes = $this->mutateFormDataBeforeFill($recordAttributes);

        $this->form->fill($recordAttributes);

        $this->callHook('afterFill');
    }
}

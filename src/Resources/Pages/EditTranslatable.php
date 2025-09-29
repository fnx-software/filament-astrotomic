<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Resources\Pages;

use Astrotomic\Translatable\Contracts\Translatable;
use Filament\Resources\Pages\EditRecord;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasLocales;
use Illuminate\Database\Eloquent\Model;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\HasActiveLocaleSwitcher;
/**
 * @mixin EditRecord
 */
trait EditTranslatable
{
    use HasLocales;
    use HasActiveLocaleSwitcher;

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

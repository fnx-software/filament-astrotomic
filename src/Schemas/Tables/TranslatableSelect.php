<?php

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Tables;

use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TranslatableSelect extends Select
{
    /**
     * Configures the component to work with an Astrotomic translatable relationship.
     *
     * @param string $name The name of the relationship.
     * @param string $titleAttribute The attribute on the translation model to use as the option label.
     * @param Closure|null $modifyQueryUsing A closure to modify the query.
     * @return static
     */
    public function translatableRelationship(string $name, string $titleAttribute, ?Closure $modifyQueryUsing = null): static
    {
        // Set the relationship details for Filament's internal mechanics
        $this->relationship($name, $titleAttribute, $modifyQueryUsing);

        // Override the options logic to fetch translated options
        $this->options(function () use ($name, $titleAttribute, $modifyQueryUsing) {
            $relationship = $this->getRelationship();
            /** @var Builder $query */
            $query = $relationship->getRelated()->query()->translatedIn(app()->getLocale());

            if ($modifyQueryUsing) {
                $query = $this->evaluate($modifyQueryUsing, [
                    'query' => $query,
                ]);
            }

            return $query
                ->get()
                ->mapWithKeys(function (Model $record) use ($titleAttribute) {
                    // Assuming the record has the Translatable trait
                    $translatedValue = $record->translate(app()->getLocale())?->{$titleAttribute};
                    return [$record->getKey() => $translatedValue];
                });
        });

        // Override the search logic to search within translations
        $this->getSearchResultsUsing(function (string $search) use ($name, $titleAttribute, $modifyQueryUsing): array {
            $relationship = $this->getRelationship();
            /** @var Builder $query */
            $query = $relationship->getRelated()->query();

            if ($modifyQueryUsing) {
                $query = $this->evaluate($modifyQueryUsing, [
                    'query' => $query,
                ]);
            }

            // Apply the translation search constraint
            $query->whereTranslationLike($titleAttribute, "%{$search}%", app()->getLocale());

            return $query
                ->limit(50)
                ->get()
                ->mapWithKeys(function (Model $record) use ($titleAttribute) {
                    $translatedValue = $record->translate(app()->getLocale())?->{$titleAttribute};
                    return [$record->getKey() => $translatedValue];
                })
                ->toArray();
        });

        // Override the logic to get the label for the currently selected option
        $this->getOptionLabelUsing(function ($value) use ($titleAttribute): ?string {
            $record = $this->getRelationship()->getRelated()->find($value);
            return $record?->translate(app()->getLocale())?->{$titleAttribute};
        });

        return $this;
    }
}

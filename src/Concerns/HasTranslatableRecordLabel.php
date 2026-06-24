<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasTranslatableRecordLabel
{
    protected ?Closure $translatableLabelUsing = null;

    protected array $translatableSearchAttributes = [];

    public function translatableLabelUsing(?Closure $callback): static
    {
        $this->translatableLabelUsing = $callback;

        return $this;
    }

    public function translatableSearchAttributes(array $attributes): static
    {
        $this->translatableSearchAttributes = $attributes;

        return $this;
    }

    protected function resolveTranslatableRecordLabel(
        ?Model $record,
        string $fallbackAttribute,
        ?string $locale = null,
    ): ?string {
        if (! $record) {
            return null;
        }

        $locale ??= app()->getLocale();

        if ($this->translatableLabelUsing instanceof Closure) {
            return (string) app()->call($this->translatableLabelUsing, [
                'record' => $record,
                'model' => $record,
                'locale' => $locale,
            ]);
        }

        $translatedValue = method_exists($record, 'translate')
            ? $record->translate($locale)?->{$fallbackAttribute}
            : null;

        return (string) ($translatedValue
            ?? $record->{$fallbackAttribute}
            ?? $record->getKey());
    }

    protected function getTranslatableSearchAttributes(string $fallbackAttribute): array
    {
        return filled($this->translatableSearchAttributes)
            ? $this->translatableSearchAttributes
            : [$fallbackAttribute];
    }

    protected function applyTranslatableSearchAttributes(
        Builder $query,
        string $search,
        string $fallbackAttribute,
        ?string $locale = null,
    ): Builder {
        $locale ??= app()->getLocale();

        return $query->where(function (Builder $query) use ($fallbackAttribute, $locale, $search): void {
            foreach ($this->getTranslatableSearchAttributes($fallbackAttribute) as $attribute) {
                if (
                    method_exists($query->getModel(), 'isTranslationAttribute')
                    && $query->getModel()->isTranslationAttribute($attribute)
                ) {
                    $query->orWhereTranslationLike($attribute, "%{$search}%", $locale);

                    continue;
                }

                $query->orWhere($attribute, 'like', "%{$search}%");
            }
        });
    }
}

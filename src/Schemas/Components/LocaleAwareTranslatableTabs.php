<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Components;

use Closure;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;

/**
 * Filament 5 native tabs for Astrotomic translations.
 *
 * All configured locales are available by default, even if they do not yet
 * have translations. Visibility only affects the form, never stored data.
 * The main locale is always shown so its required fields stay accessible.
 */
class LocaleAwareTranslatableTabs extends Tabs
{
    protected ?Closure $localeSchema = null;

    protected array | Closure | null $selectedLocales = null;

    protected array | Closure $excludedLocales = [];

    protected array | Closure $leadingTabs = [];

    protected array | Closure $trailingTabs = [];

    protected ?Closure $nameGenerator = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tabs(fn (): array => [
            ...$this->evaluate($this->leadingTabs),
            ...$this->getLocaleTabs(),
            ...$this->evaluate($this->trailingTabs),
        ]);
    }

    /**
     * @param  callable(TranslatableTab): array  $schema
     */
    public function localeTabSchema(callable $schema): static
    {
        $this->localeSchema = $schema instanceof Closure
            ? $schema
            : Closure::fromCallable($schema);

        return $this;
    }

    /**
     * Limit visible locale tabs on this form (the main locale remains visible).
     * This does not delete translations in other locales.
     *
     * @param  array<int, string>|Closure(): array<int, string>  $locales
     */
    public function visibleLocales(array | Closure $locales): static
    {
        $this->selectedLocales = $locales;

        return $this;
    }

    /**
     * Hide locale tabs on this form without changing stored translations.
     *
     * @param  array<int, string>|Closure(): array<int, string>  $locales
     */
    public function hideLocales(array | Closure $locales): static
    {
        $this->excludedLocales = $locales;

        return $this;
    }

    /**
     * Append non-translation Filament tabs after the locale tabs.
     *
     * @param  array<int, Tab>|Closure(): array<int, Tab>  $tabs
     */
    public function appendTabs(array | Closure $tabs): static
    {
        $this->trailingTabs = $tabs;

        return $this;
    }

    /**
     * Prepend non-translation Filament tabs before the locale tabs.
     *
     * @param  array<int, Tab>|Closure(): array<int, Tab>  $tabs
     */
    public function prependTabs(array | Closure $tabs): static
    {
        $this->leadingTabs = $tabs;

        return $this;
    }

    public function makeNameUsing(?Closure $callback): static
    {
        $this->nameGenerator = $callback;

        return $this;
    }

    public function makeNameUsingPlainSyntax(): static
    {
        return $this->makeNameUsing(
            fn (string $name, string $locale): string => "{$name}:{$locale}",
        );
    }

    /**
     * The main locale cannot be excluded to avoid hiding required fields.
     *
     * @return array<int, string>
     */
    public function getVisibleLocales(): array
    {
        $plugin = FilamentAstrotomicPlugin::get();
        $mainLocale = $plugin->getMainLocale();
        $configured = $plugin->getLocales();

        $selected = $this->selectedLocales === null
            ? $configured
            : $this->evaluate($this->selectedLocales);

        $excluded = $this->evaluate($this->excludedLocales);

        $selected = is_array($selected) ? $selected : [];
        $excluded = is_array($excluded) ? $excluded : [];

        $visible = [];

        foreach ($configured as $locale) {
            if (
                $locale === $mainLocale ||
                (in_array($locale, $selected, true) && ! in_array($locale, $excluded, true))
            ) {
                $visible[] = $locale;
            }
        }

        return array_values(array_unique($visible));
    }

    /**
     * @return array<int, Tab>
     */
    protected function getLocaleTabs(): array
    {
        if ($this->localeSchema === null) {
            return [];
        }

        $plugin = FilamentAstrotomicPlugin::get();
        $mainLocale = $plugin->getMainLocale();
        $tabs = [];

        foreach ($this->getVisibleLocales() as $locale) {
            $tab = Tab::make($locale)
                ->label($plugin->getLocaleLabel($locale) ?? strtoupper($locale));

            $context = new TranslatableTab($tab, $locale, $mainLocale);
            $context->makeNameUsing($this->nameGenerator);

            $schema = $this->evaluate(
                $this->localeSchema,
                namedInjections: ['translatableTab' => $context],
                typedInjections: [TranslatableTab::class => $context],
            );

            $tabs[] = $tab->schema(is_array($schema) ? $schema : []);
        }

        return $tabs;
    }
}

<?php

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Components;

use Closure;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;

class TranslatableTabs extends Tabs
{
    protected FilamentAstrotomicPlugin $plugin;

    /**
     * Callback to generate the name of the tab.
     */
    protected ?Closure $nameGenerator = null;

    /**
     * Available locales of the application.
     */
    protected array $availableLocales;

    /**
     * Available custom locales of the application.
     */
    protected array $locales;

    /**
     * Available custom locales of the application.
     */
    protected array $customLocales = [];

    /**
     * Main locale of the application.
     */
    protected string $mainLocale;

    /**
     * Holds the tabs that will be prepended to the tabs.
     */
    protected array $prependTabs = [];

    /**
     * Holds the schema callback for localised tabs.
     */
    protected ?Closure $localeTabSchema = null;

    /**
     * Holds the tabs that will be appended to the tabs.
     */
    protected array $appendTabs = [];

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FilamentAstrotomicPlugin $plugin */
        $this->plugin = $plugin = filament('filament-astrotomic');

        $this->availableLocales = $plugin->allLocales();
        $this->mainLocale = $plugin->getMainLocale();
        $this->locales = $plugin->getLocales();

        /**
         * Merge all tabs in the correct order.
         * The closure ensures this is evaluated per-instance (crucial for Repeaters).
         */
        $this->tabs(fn () => [
            ...$this->prependTabs,
            ...$this->getGeneratedLocaleTabs(),
            ...$this->appendTabs,
        ]);
    }

    /**
     * Set the callback to generate the name of the tab.
     *
     * @param  Closure(string $name, string $locale):string|null  $callback
     */
    public function makeNameUsing(?Closure $callback): static
    {
        return $this->tap(fn () => $this->nameGenerator = $callback);
    }

    /**
     * Set the name of the tab using plain syntax `{$name}:{$locale}`.
     */
    public function makeNameUsingPlainSyntax(): static
    {
        return $this->makeNameUsing(fn (string $name, string $locale) => "{$name}:{$locale}");
    }

    /**
     * Stores the schema callback. Generation is deferred to getGeneratedLocaleTabs().
     *
     * @param  callable(TranslatableTab):(array<Component>|Closure)  $tabSchema
     */
    public function localeTabSchema(callable $tabSchema): self
    {
        $this->localeTabSchema = $tabSchema;

        return $this;
    }

    /**
     * Internal method to generate tabs dynamically.
     */
    protected function getGeneratedLocaleTabs(): array
    {
        if (! $this->localeTabSchema) {
            return [];
        }

        $languages = $this->customLocales
            ?: (! empty($this->locales) ? $this->locales : $this->availableLocales);

        return collect($languages)
            ->map(function (string $locale) {
                $tab = Tab::make($locale)
                    ->label($this->plugin->getLocaleLabel($locale));

                $translatableTab = new TranslatableTab($tab, $locale, $this->mainLocale);

                $translatableTab->makeNameUsing($this->nameGenerator);

                // Evaluate the stored schema callback
                $schema = $this->evaluate(
                    $this->localeTabSchema,
                    namedInjections: ['translatableTab' => $translatableTab],
                    typedInjections: [TranslatableTab::class => $translatableTab],
                );

                return $tab->schema($schema);
            })
            ->all();
    }

    /**
     * Prepends tabs before localised tabs.
     *
     * @param  array|callable():(array)  $tabs
     * @return $this
     */
    public function prependTabs(array | callable $tabs = []): self
    {
        $this->prependTabs = $this->evaluate($tabs);

        return $this;
    }

    public function customLocales(array | callable $locales = []): self
    {
        $this->customLocales = $this->evaluate($locales);

        return $this;
    }

    /**
     * Appends tabs after localised tabs.
     *
     * @param  array|callable():(array)  $tabs
     * @return $this
     */
    public function appendTabs(array | callable $tabs = []): self
    {
        $this->appendTabs = $this->evaluate($tabs);

        return $this;
    }
}

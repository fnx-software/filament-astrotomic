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

    protected ?Closure $nameGenerator = null;

    protected array $availableLocales;

    protected array $locales;

    protected array $customLocales = [];

    protected string $mainLocale;

    protected array $prependTabs = [];

    protected ?Closure $localeTabSchema = null;

    protected array $appendTabs = [];

    /**
     * If true, tabs will be shown even if there is only one locale.
     */
    protected bool $isForced = false;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FilamentAstrotomicPlugin $plugin */
        $this->plugin = $plugin = filament('filament-astrotomic');

        $this->availableLocales = $plugin->allLocales();
        $this->mainLocale = $plugin->getMainLocale();
        $this->locales = $plugin->getLocales();

        $this->tabs(fn () => [
            ...$this->prependTabs,
            ...$this->getGeneratedLocaleTabs(),
            ...$this->appendTabs,
        ]);
    }

    public function force(bool $condition = true): static
    {
        $this->isForced = $condition;

        return $this;
    }

    public function makeNameUsing(?Closure $callback): static
    {
        return $this->tap(fn () => $this->nameGenerator = $callback);
    }

    public function makeNameUsingPlainSyntax(): static
    {
        return $this->makeNameUsing(fn (string $name, string $locale) => "{$name}:{$locale}");
    }

    public function localeTabSchema(callable $tabSchema): self
    {
        $this->localeTabSchema = $tabSchema;

        return $this;
    }

    protected function getGeneratedLocaleTabs(): array
    {
        if (! $this->localeTabSchema) {
            return [];
        }

        $languages = $this->customLocales
            ?: (! empty($this->locales) ? $this->locales : $this->availableLocales);

        $languagesList = is_array($languages) ? $languages : $languages->toArray();

        // Check local force OR global plugin force
        $shouldForce = $this->isForced || $this->plugin->isForced();

        // --- SINGLE LOCALE LOGIC ---
        if (count($languagesList) === 1 && ! $shouldForce) {
            $locale = array_values($languagesList)[0];

            // Switch to Grid view
            $this->view('filament-schemas::components.grid');

            $tab = Tab::make($locale)
                ->label($this->plugin->getLocaleLabel($locale));

            $translatableTab = new TranslatableTab($tab, $locale, $this->mainLocale);
            $translatableTab->makeNameUsing($this->nameGenerator);

            $schema = $this->evaluate(
                $this->localeTabSchema,
                namedInjections: ['translatableTab' => $translatableTab],
                typedInjections: [TranslatableTab::class => $translatableTab],
            );

            $this->schema($schema);

            return [];
        }

        // --- MULTI LOCALE LOGIC ---
        return collect($languages)
            ->map(function (string $locale) {
                $tab = Tab::make($locale)
                    ->label($this->plugin->getLocaleLabel($locale));

                $translatableTab = new TranslatableTab($tab, $locale, $this->mainLocale);

                $translatableTab->makeNameUsing($this->nameGenerator);

                $schema = $this->evaluate(
                    $this->localeTabSchema,
                    namedInjections: ['translatableTab' => $translatableTab],
                    typedInjections: [TranslatableTab::class => $translatableTab],
                );

                return $tab->schema($schema);
            })
            ->all();
    }

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

    public function appendTabs(array | callable $tabs = []): self
    {
        $this->appendTabs = $this->evaluate($tabs);

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Tabs\Tab;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class TranslatableTabs extends AdvancedTabs
{
    protected FilamentAstrotomicPlugin $plugin;

    protected ?Closure $nameGenerator = null;

    protected array $availableLocales = [];

    protected array $locales = [];

    protected array $customLocales = [];

    protected string $mainLocale;

    protected array $prependTabs = [];

    protected ?Closure $localeTabSchema = null;

    protected array $appendTabs = [];

    protected bool $isForced = false;

    protected ?string $actionPickerName = null;

    protected string|Htmlable|Closure|null $actionPickerLabel = null;

    protected string|Closure|null $actionPickerIcon = null;

    protected bool $localePickerAsButton = false;

    protected array|Closure|null|string $actionPickerColor = null;

    protected Closure|Htmlable|null|string $actionPickerModalHeading = null;

    protected Closure|Htmlable|null|string $actionPickerModalDescription = null;

    protected Closure|Htmlable|null|string $actionPickerModalSubmitActionLabel = null;

    protected bool|Closure $hasSuffixLocalePicker = false;

    protected bool|Closure $hasPrefixLocalePicker = false;

    protected ?array $cachedVisibleLocales = null;

    protected bool $isResolvingVisibleLocales = false;

    public function hasSuffixLocalePicker(bool|Closure $condition = true): static
    {
        $this->hasSuffixLocalePicker = $condition;

        $this->suffixAction(
            fn (): Action => $this->makeLocalePickerAction()
                ->visible(fn (): bool => $this->isSuffixLocalePickerEnabled())
        );

        return $this;
    }

    public function hasPrefixLocalePicker(bool|Closure $condition = true): static
    {
        $this->hasPrefixLocalePicker = $condition;

        $this->prefixAction(
            fn (): Action => $this->makeLocalePickerAction()
                ->visible(fn (): bool => $this->isPrefixLocalePickerEnabled())
        );

        return $this;
    }

    public function isSuffixLocalePickerEnabled(): bool
    {
        return (bool) $this->evaluate($this->hasSuffixLocalePicker);
    }

    public function isPrefixLocalePickerEnabled(): bool
    {
        return (bool) $this->evaluate($this->hasPrefixLocalePicker);
    }

    public function actionPickerName(?string $actionPickerName): static
    {
        $this->actionPickerName = $actionPickerName;

        return $this;
    }

    public function getActionPickerName(): ?string
    {
        return $this->evaluate($this->actionPickerName);
    }

    public function actionPickerLabel(string|Htmlable|Closure|null $actionPickerLabel): static
    {
        $this->actionPickerLabel = $actionPickerLabel;

        return $this;
    }

    public function getActionPickerLabel(): string|Htmlable|null
    {
        return $this->evaluate($this->actionPickerLabel);
    }

    public function actionPickerIcon(string|Closure|null $actionPickerIcon): static
    {
        $this->actionPickerIcon = $actionPickerIcon;

        return $this;
    }

    public function getActionPickerIcon(): ?string
    {
        return $this->evaluate($this->actionPickerIcon);
    }

    public function actionPickerModalHeading(string|Htmlable|Closure|null $actionPickerModalHeading): static
    {
        $this->actionPickerModalHeading = $actionPickerModalHeading;

        return $this;
    }

    public function getActionPickerModalHeading(): string|Htmlable|null
    {
        return $this->evaluate($this->actionPickerModalHeading);
    }

    public function actionPickerModalDescription(string|Htmlable|Closure|null $actionPickerModalDescription): static
    {
        $this->actionPickerModalDescription = $actionPickerModalDescription;

        return $this;
    }

    public function getActionPickerModalDescription(): string|Htmlable|null
    {
        return $this->evaluate($this->actionPickerModalDescription);
    }

    public function actionPickerModalSubmitActionLabel(string|Htmlable|Closure|null $actionPickerModalSubmitActionLabel): static
    {
        $this->actionPickerModalSubmitActionLabel = $actionPickerModalSubmitActionLabel;

        return $this;
    }

    public function getActionPickerModalSubmitActionLabel(): string|Htmlable|null
    {
        return $this->evaluate($this->actionPickerModalSubmitActionLabel);
    }

    public function actionPickerColor(string|Closure|null $actionPickerColor): static
    {
        $this->actionPickerColor = $actionPickerColor;

        return $this;
    }

    public function getActionPickerColor(): string|array|null
    {
        return $this->evaluate($this->actionPickerColor);
    }

    public function localePickerAsButton(bool $localePickerAsButton): static
    {
        $this->localePickerAsButton = $localePickerAsButton;

        return $this;
    }

    public function getLocalePickerAsButton(): bool
    {
        return (bool) $this->evaluate($this->localePickerAsButton);
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FilamentAstrotomicPlugin $plugin */
        $this->plugin = $plugin = filament('filament-astrotomic');

        $this->availableLocales = $plugin->allLocales();
        $this->mainLocale = $plugin->getMainLocale();
        $this->locales = $plugin->getLocales();

        $this->tabs(fn (): array => [
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
        return $this->makeNameUsing(
            fn (string $name, string $locale): string => "{$name}:{$locale}",
        );
    }

    public function localeTabSchema(callable $tabSchema): self
    {
        $this->localeTabSchema = $tabSchema instanceof Closure
            ? $tabSchema
            : Closure::fromCallable($tabSchema);

        return $this;
    }

    protected function makeLocalePickerAction(): Action
    {
        $manageableLocales = $this->getManageableLocaleOptions();

        $action = Action::make($this->resolveActionPickerName())
            ->label($this->getActionPickerLabel() ?? __('filament-astrotomic::actions.modal.heading'))
            ->icon($this->getActionPickerIcon() ?? 'heroicon-m-language')
            ->color($this->getActionPickerColor() ?? 'primary')
            ->visible(fn (): bool => count($manageableLocales) > 0)
            ->modalHeading(
                fn () => $this->getActionPickerModalHeading()
                    ?? __('filament-astrotomic::actions.modal.heading')
            )
            ->modalDescription(
                fn () => $this->getActionPickerModalDescription()
                    ?? __('filament-astrotomic::actions.modal.description')
            )
            ->modalSubmitActionLabel(
                fn () => $this->getActionPickerModalSubmitActionLabel()
                    ?? __('filament-astrotomic::actions.modal.submit')
            )
            // Pre-fill the checkboxes with currently visible languages (excluding main)
            ->mountUsing(function ($form) {
                $form->fill([
                    'locales' => array_values(array_diff($this->getVisibleLocales(), [$this->mainLocale])),
                ]);
            })
            ->schema([
                CheckboxList::make('locales')
                    ->hiddenLabel()
                    ->options($manageableLocales)
                    // Show a warning if the language already has data in the DB/State
                    ->descriptions(function () use ($manageableLocales) {
                        $descriptions = [];
                        $detected = $this->detectExistingLocales();
                        foreach ($manageableLocales as $locale => $label) {
                            if (in_array($locale, $detected)) {
                                $descriptions[$locale] = 'Has existing data';
                            }
                        }

                        return $descriptions;
                    })
                    ->columns(),
            ])
            ->action(fn (array $data) => $this->syncVisibleLocales(
                Arr::wrap($data['locales'] ?? [])
            ));

        if ($this->getLocalePickerAsButton()) {
            $action->button();
        } else {
            $action->iconButton();
        }

        return $action;
    }

    protected function getGeneratedLocaleTabs(): array
    {
        if (! $this->localeTabSchema instanceof Closure) {
            return [];
        }

        $localeOptions = $this->getConfiguredLocaleOptions();
        $localeCodes = array_keys($localeOptions);

        $shouldForce = $this->isForced
            || $this->plugin->isForced()
            || $this->isPrefixLocalePickerEnabled()
            || $this->isSuffixLocalePickerEnabled();

        if ((count($localeCodes) === 1) && (! $shouldForce)) {
            $locale = $localeCodes[0];

            $this->view('filament-schemas::components.grid');

            $tab = Tab::make($locale)
                ->label($localeOptions[$locale]);

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

        return collect($localeOptions)
            ->map(function (string $label, string $locale): Tab {
                $tab = Tab::make($locale)
                    ->label($label)
                    ->visible(fn (): bool => $this->isLocaleVisible($locale));

                $translatableTab = new TranslatableTab($tab, $locale, $this->mainLocale);
                $translatableTab->makeNameUsing($this->nameGenerator);

                $schema = $this->evaluate(
                    $this->localeTabSchema,
                    namedInjections: ['translatableTab' => $translatableTab],
                    typedInjections: [TranslatableTab::class => $translatableTab],
                );

                return $tab->schema($schema);
            })
            ->values()
            ->all();
    }

    protected function getManageableLocaleOptions(): array
    {
        $configuredLocales = $this->getConfiguredLocaleOptions();

        // Remove the Main Locale so the user cannot uncheck/delete it
        if (array_key_exists($this->mainLocale, $configuredLocales)) {
            unset($configuredLocales[$this->mainLocale]);
        }

        return $configuredLocales;
    }

    protected function syncVisibleLocales(array $selectedLocales): void
    {
        $selectedLocales = $this->normalizeLocaleCodes($selectedLocales);

        // Always enforce the Main Locale to be visible
        $newVisibleLocales = array_values(array_unique([$this->mainLocale, ...$selectedLocales]));
        $oldVisibleLocales = $this->getVisibleLocales();

        $localesToRemove = array_diff($oldVisibleLocales, $newVisibleLocales);
        $localesToAdd = array_diff($newVisibleLocales, $oldVisibleLocales);

        $livewire = $this->getLivewire();

        $record = null;
        if (method_exists($livewire, 'getRecord')) {
            try {
                $record = $livewire->getRecord();
            } catch (Throwable $e) {
            }
        } elseif (property_exists($livewire, 'record')) {
            $record = $livewire->record;
        }

        // Process removals
        foreach ($localesToRemove as $locale) {
            if ($locale === $this->mainLocale) {
                continue;
            } // Safety check

            // 1. Unset data from Livewire state to clear the UI inputs
            $dataPath = $this->getStatePath();
            if (blank($dataPath)) {
                $dataPath = 'data';
            }
            $currentData = data_get($livewire, $dataPath);
            if (is_array($currentData) && array_key_exists($locale, $currentData)) {
                unset($currentData[$locale]);
                data_set($livewire, $dataPath, $currentData);
            }

            // 2. Delete the translation immediately from DB
            if ($record && method_exists($record, 'deleteTranslation')) {
                $record->deleteTranslation($locale);
            }
        }

        // Save new visible configuration
        $this->cachedVisibleLocales = $newVisibleLocales;
        $this->storeVisibleLocales($newVisibleLocales);

        // Figure out which tab to focus on automatically
        // If we just added a language, focus on the first added one.
        // If we only deleted languages, focus back on the main locale.
        $tabToFocus = ! empty($localesToAdd) ? array_values($localesToAdd)[0] : $this->mainLocale;

        $this->getLivewire()->dispatch('astrotomic-tab-switched', tab: $tabToFocus, id: $this->getId());

        if (method_exists($this, 'getLivewireProperty')) {
            $livewireProperty = $this->getLivewireProperty();
            if (filled($livewireProperty)) {
                data_set($livewire, $livewireProperty, $tabToFocus);
            }
        }
    }

    protected function isLocaleVisible(string $locale): bool
    {
        $visibleLocales = $this->getVisibleLocales();

        return in_array($locale, $visibleLocales, true);
    }

    protected function getVisibleLocales(): array
    {
        if (is_array($this->cachedVisibleLocales)) {
            return $this->cachedVisibleLocales;
        }

        if ($this->isResolvingVisibleLocales) {
            return [$this->mainLocale];
        }

        $this->isResolvingVisibleLocales = true;

        try {
            $allowedLocales = array_keys($this->getConfiguredLocaleOptions());

            $storedLocales = session()->get($this->getVisibleLocalesStorePath());

            if (is_array($storedLocales) && $storedLocales !== []) {
                return $this->cachedVisibleLocales = $this->normalizeLocaleCodes(
                    $storedLocales,
                    $allowedLocales,
                );
            }

            return $this->cachedVisibleLocales = $this->normalizeLocaleCodes([
                $this->mainLocale,
                ...$this->detectExistingLocales(),
            ], $allowedLocales);
        } finally {
            $this->isResolvingVisibleLocales = false;
        }
    }

    protected function storeVisibleLocales(array $locales): void
    {
        session()->put(
            $this->getVisibleLocalesStorePath(),
            $this->normalizeLocaleCodes([
                $this->mainLocale,
                ...$locales,
            ]),
        );
    }

    protected function detectExistingLocales(): array
    {
        $detectedLocales = [];
        $configuredLocales = array_keys($this->getConfiguredLocaleOptions());

        foreach ($this->getTranslationSources() as $source) {
            foreach ($configuredLocales as $locale) {
                if ($this->localeHasMeaningfulData(data_get($source, $locale))) {
                    $detectedLocales[] = $locale;
                }
            }
        }

        return array_values(array_unique($detectedLocales));
    }

    protected function getTranslationSources(): array
    {
        $sources = [];
        $livewire = $this->getLivewire();

        $record = null;

        if (method_exists($livewire, 'getRecord')) {
            try {
                $record = $livewire->getRecord();
            } catch (Throwable) {
                $record = null;
            }
        } elseif (property_exists($livewire, 'record')) {
            $record = $livewire->record;
        }

        if ($record && method_exists($record, 'getTranslationsArray')) {
            $sources[] = $record->getTranslationsArray();
        }

        foreach (['data', 'mountedActionsData'] as $property) {
            $state = data_get($livewire, $property);

            if (is_array($state) && $state !== []) {
                $sources[] = $state;
            }
        }

        return $sources;
    }

    protected function localeHasMeaningfulData(mixed $value): bool
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->localeHasMeaningfulData($item)) {
                    return true;
                }
            }

            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_numeric($value)) {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        return filled($value);
    }

    protected function getConfiguredLocaleOptions(): array
    {
        $locales = $this->customLocales !== []
            ? $this->customLocales
            : (! empty($this->locales) ? $this->locales : $this->availableLocales);

        $options = [];

        foreach ($locales as $key => $value) {
            if (is_string($key) && ! is_numeric($key)) {
                $options[$key] = (string) $value;

                continue;
            }

            $locale = (string) $value;
            $options[$locale] = $this->plugin->getLocaleLabel($locale) ?? $locale;
        }

        if (! array_key_exists($this->mainLocale, $options)) {
            $options = [
                $this->mainLocale => $this->plugin->getLocaleLabel($this->mainLocale) ?? $this->mainLocale,
                ...$options,
            ];
        }

        return $options;
    }

    protected function normalizeLocaleCodes(array $locales, ?array $allowedLocales = null): array
    {
        $allowedLocales ??= array_keys($this->getConfiguredLocaleOptions());

        $normalized = [];

        foreach ($locales as $key => $value) {
            $locale = (is_string($key) && ! is_numeric($key))
                ? $key
                : (string) $value;

            if (! in_array($locale, $allowedLocales, true)) {
                continue;
            }

            if (in_array($locale, $normalized, true)) {
                continue;
            }

            $normalized[] = $locale;
        }

        return $normalized;
    }

    protected function getVisibleLocalesStorePath(): string
    {
        $key = (string) (
            $this->getKey()
            ?? $this->getId()
            ?? $this->getLabel()
            ?? 'translatable-tabs'
        );

        $normalizedKey = (string) Str::of($key)
            ->replaceMatches('/[^A-Za-z0-9_]+/', '_')
            ->trim('_');

        return 'astrotomic_visible_locales_'.$this->getLivewire()->getId().'_'.$normalizedKey;
    }

    protected function resolveActionPickerName(): string
    {
        $name = $this->getActionPickerName();

        if (filled($name)) {
            return $name;
        }

        $key = (string) (
            $this->getKey()
            ?? $this->getId()
            ?? $this->getLabel()
            ?? 'translatable-tabs'
        );

        return 'manage_locales_'.(string) Str::of($key)
            ->replaceMatches('/[^A-Za-z0-9_]+/', '_')
            ->trim('_');
    }

    public function prependTabs(array|callable $tabs = []): self
    {
        $this->prependTabs = $this->evaluate($tabs);

        return $this;
    }

    public function customLocales(array|callable $locales = []): self
    {
        $this->customLocales = $this->evaluate($locales);

        $this->cachedVisibleLocales = null;

        return $this;
    }

    public function appendTabs(array|callable $tabs = []): self
    {
        $this->appendTabs = $this->evaluate($tabs);

        return $this;
    }
}

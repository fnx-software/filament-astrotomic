# v2.2.0 pre-release gate (no tag created)

## Review findings
- Main contains Filament 5 embedded HTML tabs implementation and LocaleAwareTranslatableTabs.
- The previous CI only ran on fix branches / pull requests and only executed the locale-aware test; this branch adds main and the full Pest suite + Pint on PHP 8.3/8.4.
- Package Testbench bootstrap did not set the current Filament panel; LocaleAwareTranslatableTabs::getVisibleLocales() requires the plugin. This branch registers an in-memory panel with ar/en/fr test locales.
- In TranslatableTabs::syncVisibleLocales(), the Livewire-property path referred to an undefined $livewire variable. It now obtains the actual livewire component when setting the property.
- Hiding an optional locale no longer deletes database translations (already fixed in main).
- **No release/tag** until CI green and manual create/edit translations and locale picker tested.

## Commands
```bash
composer install
composer test:lint
composer test
```

Manual integration: run RDMS PR #46 (after tagged v2.2.0 and composer.lock update).

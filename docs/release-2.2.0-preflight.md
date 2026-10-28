# v2.2.0 pre-release gate (no tag created)

## Review findings
- Main initially contained the Filament 5 embedded HTML tab fix and an unreleased LocaleAwareTranslatableTabs workaround. The v2.2 preflight branch removes that duplicate before release.
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

## CI verified on preflight branch

GitHub Actions run 40685709752 passed both PHP 8.3 and 8.4 jobs. Composer dependency installation, PHP lint, Pint and the current Pest suite all passed on commit `ddb13966`.

**Coverage limit:** the current Pest suite primarily checks component structure and locale visibility, not real browser Create/Edit tab panels or translation persistence. A passing CI run is necessary but not sufficient for a stable release. Run browser UAT on a consumer test app before tagging. The RDMS PR #46 remains blocked on a published stable v2.2.0.

## Simplified public API (2026-10-28)

Removed unreleased `LocaleAwareTranslatableTabs`, its `TranslatableLocaleTabs` alias and corresponding redundant tests. `TranslatableTabs` remains the single supported translation-tab component. All published v2.1.0 APIs are unchanged; the removed components did not exist in v2.1.0. RDMS PR #46 already uses `TranslatableTabs` and requires no code migration.


## Testbench locale configuration correction

The actual Livewire form test raised `LocalesNotDefinedException` because `Astrotomic\\Translatable\\Locales` reads `config('translatable.locales')`. Setting only `FilamentAstrotomicPlugin::locales()` does **not** initialize that configuration. `tests/TestCase.php` now configures the Astrotomic locale keys before registering the Filament test panel.

## Published tag warning

An annotated `v2.2.0` tag was pushed to commit `a5f243a` despite failing package tests. This preflight PR must not be represented as already included in that tag. Prefer a new patch release `v2.2.1` after successful verification; do not silently move an immutable public tag.

## SQLite persistence test gate

The `tests/Feature/TranslatableTabsPersistenceTest.php` suite provisions an isolated SQLite in-memory schema and exercises the real `Astrotomic\\Translatable\\Translatable` Eloquent trait from the package's Filament Livewire form. It checks Create (EN+AR), Edit (AR only), and retention of an existing FR translation excluded from the form's `customLocales(['ar', 'en'])` configuration. The fixture is not an HTTP Filament Resource page, so final browser Create/Edit/locale picker/RTL UAT is still required.


## Local persistence verification

Run the isolated database-backed regression cases independently of the browser-based smoke checks:

```bash
composer test:lint
vendor/bin/pest tests/Feature/TranslatableTabsPersistenceTest.php
composer test
composer validate --strict
```

The SQLite integration suite uses temporary in-memory tables for `TranslatedPost` and `TranslatedPostTranslation`, and checks actual rows written by Astrotomic when submitting the Filament Livewire form. It verifies independent AR/EN edits and preservation of a stored FR translation absent from the displayed locale tabs.

This does not replace manual CreateRecord/EditRecord page and JS locale-picker/browser RTL UAT. Do not treat a successful state assertion as proof of database persistence without database row assertions.

# Changelog

All notable changes to `fnx-software/filament-astrotomic` will be documented in this file.

## v1.2.8 - 2026-01-26

Remove Debug code  (Mistake)

**Full Changelog**: https://github.com/fnx-software/filament-astrotomic/compare/v1.2.7...v1.2.8

## v1.2.7 - Smart Single-Locale Layouts & Force Configuration - 2026-01-18

### ✨ What's New in V1.2.7

**Features**

* **Smart Single-Locale Display:**
  `TranslatableTabs` now automatically detects if there is only **one** locale available. If so, it switches from a "Tabs" layout to a cleaner "Grid" layout, removing the redundant tab bar while maintaining the correct data structure.
  
* **Global & Local `force()` Configuration:**
  Added the ability to force the "Tabs" layout to always appear, even if there is only one locale.
  
  **Global Usage (in Panel Provider):**
  
    ```php
    FilamentAstrotomicPlugin::make()
      ->force() // Always show tabs globally
  
  
    ```
  **Local Usage (in Form Schema):**
  
    ```php
    TranslatableTabs::make('translations')
      ->force() // Always show tabs for this component
  
  
    ```

**Improvements**

* Refactored `TranslatableTabs` to dynamically switch views (`Tabs` vs `Grid`) based on the locale count and configuration.

**Full Changelog**: https://github.com/fnx-software/filament-astrotomic/compare/v1.2.6...v1.2.7

## v1.2.6 -Fix  TranslatableTabs compatibility with Repeaters - 2026-01-15

### ✨ What's New in V1.2.6

#### Bug Fixes

- Fix TranslatableTabs inside Repeater components:
  - Resolved an issue where TranslatableTabs would not function correctly when nested inside a Filament Repeater.
    Previously, tab instances were generated immediately upon definition, causing multiple repeater rows to share the same object references and state paths.
  - Refactored localeTabSchema to defer tab generation, ensuring unique tab instances are created for every repeater row.
  

Full Changelog: [https://github.com/fnx-software/filament-astrotomic/compare/v1.2.5...v1.2.6](https://www.google.com/url?sa=E&q=https%3A%2F%2Fgithub.com%2Ffnx-software%2Ffilament-astrotomic%2Fcompare%2Fv1.2.5...v1.2.6)

## v1.2.5 — Custom locales support (Plugin + TranslatableTabs) - 2026-01-14

### ✨ What's New in V1.2.5

- **Plugin-level locales override:** Added `FilamentAstrotomicPlugin::locales(array|Closure)` to define available locales dynamically (e.g., per-tenant / database-driven) instead of relying only on `config/translatable.php`.
- **Per-form locale override:** Added `TranslatableTabs::customLocales(array|Closure)` to specify a custom locale list directly on the component.

#### Usage examples

**1) Dynamic locales from tenant (Plugin)**

```php
use Filament\Facades\Filament;

FilamentAstrotomicPlugin::make()
    ->locales(fn () => Filament::getTenant()?->locales ?? config('translatable.locales'))
    ->mainLocale(fn () => Filament::getTenant()?->locale_code ?? config('app.locale'));




```
**2) Custom locales for a specific form section (TranslatableTabs)**

```php
TranslatableTabs::make()
    ->customLocales(['ar', 'en', 'fr', 'pt'])
    ->localeTabSchema(fn (TranslatableTab $tab) => [
        TextInput::make($tab->makeName('label'))
            ->required($tab->isMainLocale()),
    ]);




```
**Notes**

* `mainLocale()` is always included in the locales list (even if not explicitly provided).
* No breaking changes; existing setups using `config/translatable.php` continue to work unchanged.

## V1.2.4 - Dynamic Field Label Helpers - 2025-10-16

### 🎉 Announcing Release V1.2.4

We're excited to announce the release of version 1.2.4 of the **Filament Astrotomic Translations** package! This release introduces a quality-of-life improvement for form development and includes documentation updates.

### ✨ What's New in V1.2.4

#### New: Dynamic Field Label Helpers

To improve clarity in multi-language forms, we've added two new helper methods to the `TranslatableTab` class. You can now automatically prepend or append the current locale's name to your field labels, making the UI more intuitive for content editors.

* `$tab->makePrefixLabel(string $name)`: Generates a label like `"Title (English)"`.
* `$tab->makeSuffixLabel(string $name)`: Generates a label like `"(English) Title"`.

**Example Usage:**

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;
use Filament\Forms\Components\TextInput;

TranslatableTabs::make()
    ->localeTabSchema(fn (TranslatableTab $tab) => [
        TextInput::make($tab->makeName('title'))
            // Renders label as "Title (English)"
            ->label($tab->makePrefixLabel('Title')),
    ])





```
#### 📝 Documentation Updates

The `README.md` has been updated to include detailed instructions and examples for the new label helper methods.

### How to Update

To upgrade to the latest version, run the following Composer command in your project's root directory:

```bash
composer update fnx-software/filament-astrotomic





```

---

You can now create a new tag and release on your GitHub repository with this information. Congratulations on the new release

## v1.2.3 - Translatable Relationship Select - 2025-09-30

### **Release Notes**

This release introduces a new, highly-requested form component to dramatically simplify working with translatable relationships: `TranslatableSelect`.

### The Problem

Previously, using a standard `Select` component for a `BelongsTo` relationship on a translatable model was cumbersome. Filament's `->relationship()` helper couldn't automatically find the translated attribute (e.g., `name`), forcing developers to write complex `->options()` and `->getSearchResultsUsing()` closures for every select input.

### 🚀 New Feature: `TranslatableSelect` Component

To solve this, we've added a new `TranslatableSelect` component with a dedicated `->translatableRelationship()` method. This brings the simplicity of Filament's built-in relationship helpers to your translatable models.

The component automatically handles:

- Loading relationship options with the correct translation for the current locale.
- Searching through translated attributes.
- Displaying the correct label for the selected option.

**Example Usage:**

Simply replace `Select` with `TranslatableSelect` and use the new method.

```php
use Fnxsoftware\FilamentAstrotomic\Forms\Components\TranslatableSelect;
use Filament\Forms\Form;

public static function form(Form $form): Form
{
    return $form->schema([
        // ... other fields in your form
        TranslatableSelect::make('country_id')
            ->translatableRelationship('country', 'name') // The magic happens here!
            ->searchable()
            ->preload()
            ->required(),
    ]);
}






```
This single line replaces complex custom logic, making your form code cleaner, more readable, and much easier to maintain. Enjoy the streamlined experience

## v1.2.1 - 2025-09-29

Fixes

## v1.2.0: Nested Relationship Support for Columns & Infolists - 2025-09-29

### **Release Notes**

This release significantly enhances the power and flexibility of the `TranslatableColumn` and `TranslatableEntry` components by adding full support for nested relationships. You can now display and search translated attributes from related models with the same simplicity as direct attributes.

### 🚀 Enhancement: Full Nested Relationship Support

Previously, these components could only work with attributes on the main resource's model (e.g., `'name'`). Attempting to use dot notation for a relationship (e.g., `'country.name'`) would not work as expected.

With this update, you can now seamlessly use dot notation to access translated attributes on related models in both your tables and infolists.

#### `TranslatableColumn` (Tables)

The `TranslatableColumn` now correctly displays the translated attribute from a related model and, crucially, makes it searchable.

**Example Usage:**

```php
use Fnxsoftware\FilamentAstrotomic\Tables\Columns\TranslatableColumn;

// This now works perfectly for both display and search!
TranslatableColumn::make('country.name')
    ->label('Country')
    ->searchable(), // Search is now relationship-aware!








```
#### `TranslatableEntry` (Infolists)

The `TranslatableEntry` has been updated to mirror this functionality, allowing for effortless display of nested translated data on your view pages.

**Example Usage:**

```php
use Fnxsoftware\FilamentAstrotomic\Infolists\Components\TranslatableEntry;

// This now works seamlessly.
TranslatableEntry::make('country.name')
    ->label('Country'),








```
### How It Works

* Both components now intelligently parse the name you provide.
* If dot notation is detected, they will automatically traverse the Eloquent relationship to fetch the correct translated value based on the `LocaleSwitcher`.
* The `TranslatableColumn`'s search functionality is also relationship-aware, automatically applying a `whereHas` constraint to properly filter your results.

This update makes the components much more powerful and consistent, allowing you to build complex, multilingual views with cleaner code.

## v1.1.2 - 2025-09-29

Fixes

## v1.1.1 - Dynamic Main Locale Configuration - 2025-09-29

### **Release Notes**

This release introduces a new level of flexibility for configuring the main locale of your application directly from the plugin.

### 🚀 Enhancements

#### Dynamic Main Locale Configuration

Previously, the main locale was strictly determined by your `config/translatable.php` file. Now, you can dynamically override this setting using the new `mainLocale()` method when registering the plugin in your Panel Provider.

This is perfect for multi-tenant applications or scenarios where the default language needs to be fetched from a database or another dynamic source.

**How to Use:**

You can provide either a static string or a Closure to the `mainLocale()` method.

**1. Set a static main locale:**

```php
// app/Providers/Filament/AdminPanelProvider.php
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;

->plugins([
    FilamentAstrotomicPlugin::make()
        ->mainLocale('ar')
])










```
**2. Set a dynamic locale using a Closure:**

```php
// app/Providers/Filament/AdminPanelProvider.php
use App\Models\Setting;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;

->plugins([
    FilamentAstrotomicPlugin::make()
        ->mainLocale(fn () => Setting::where('key', 'default_locale')->first()?->value ?? 'en')
])










```
If the `mainLocale()` method is not called, the plugin will fall back to the default behavior of reading from your configuration file, ensuring backward compatibility.

Enjoy the new flexibility

## v1.0 - 2025-09-29

This release introduces a suite of powerful new features designed to streamline the experience of displaying and interacting with translated content on your List and View pages.

### ✨ New Features

#### 1. Locale Switcher Action

A new `LocaleSwitcher` header action has been added. When placed on a List or View page, it allows users to dynamically change the language for the content being displayed.

```php
// Add to your List or View page's getHeaderActions() method
use Fnxsoftware\FilamentAstrotomic\Actions\LocaleSwitcher;

protected function getHeaderActions(): array
{
    return [
        // ...your other actions
        LocaleSwitcher::make(),
    ];
}











```
#### 2. `TranslatableColumn` for Tables

Say goodbye to complex closures in your table definitions! The new `TranslatableColumn` is a dedicated component that:

* **Reacts automatically** to the `LocaleSwitcher`.
* Provides **out-of-the-box search functionality** for translated attributes using `whereTranslationLike`.

Simply replace your `TextColumn` with `TranslatableColumn` for translated fields:

```php
use Fnxsoftware\FilamentAstrotomic\Tables\Columns\TranslatableColumn;

->columns([
    TranslatableColumn::make('name')
        ->searchable()
        ->sortable(),
    // ...
])











```
#### 3. `TranslatableEntry` for Infolists

Matching the convenience of the new column, the `TranslatableEntry` component simplifies how you display translated content on View pages. It automatically listens for changes from the `LocaleSwitcher` and displays the correct translation.

```php
use Fnxsoftware\FilamentAstrotomic\Infolists\Components\TranslatableEntry;

->schema([
    TranslatableEntry::make('name'),
    TranslatableEntry::make('description'),
    // ...
])











```
These components work together to create a seamless and reactive multilingual experience for both developers and users. We hope you enjoy the cleaner code and improved functionality

## 1.0.0 - 202X-XX-XX

- initial release

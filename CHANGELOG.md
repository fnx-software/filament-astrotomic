# Changelog

All notable changes to `fnx-software/filament-astrotomic` will be documented in this file.

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

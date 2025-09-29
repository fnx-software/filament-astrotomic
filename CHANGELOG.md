# Changelog

All notable changes to `fnx-software/filament-astrotomic` will be documented in this file.

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

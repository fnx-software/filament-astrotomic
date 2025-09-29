# Changelog

All notable changes to `fnx-software/filament-astrotomic` will be documented in this file.

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

# Filament Astrotomic Translations

[![Total Downloads](https://img.shields.io/packagist/dt/fnx-software/filament-astrotomic)](https://packagist.org/packages/fnx-software/filament-astrotomic)

This package is an extension for **Filament v5** and [laravel-translatable](https://docs.astrotomic.info/laravel-translatable) to easily manage multilingual content in your admin panel.

For **Filament v4**, use an older compatible release/tag.

This is an enhanced fork of [doriiaan/filament-astrotomic](https://github.com/Doriiaan/filament-astrotomic) and the original [cactus-galaxy/filament-astrotomic](https://github.com/CactusGalaxy/FilamentAstrotomic), updated for Filament 5 and introducing powerful new features like a reactive `LocaleSwitcher`, dedicated components for displaying translated content, and translated relationship selects with custom option labels.

<img width="3072" height="892" alt="CleanShot 2025-09-29 at 12 02 44@2x" src="https://github.com/user-attachments/assets/a2ef2b14-db7b-4d99-8fc9-dd30ed81a4a7" />

## Installation

You can install the package via Composer:

```bash
composer require fnx-software/filament-astrotomic
```

Publish the configuration for `astrotomic/laravel-translatable`:

```bash
php artisan vendor:publish --tag="translatable"
```

Configure the locales your app should use in `config/translatable.php`:

```php
// config/translatable.php
'locales' => [
    'en',
    'es',
    'fr',
],
```

## Setup

### Adding the Plugin to a Panel

Register the plugin in your Panel Provider:

```php
// app/Providers/Filament/AdminPanelProvider.php

use Filament\Panel;
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentAstrotomicPlugin::make(),
        ]);
}
```

### Customizing the Main Locale

By default, the main locale is taken from your `config/translatable.php` file. You can override this dynamically when registering the plugin.

Set a static main locale:

```php
FilamentAstrotomicPlugin::make()
    ->mainLocale('ar')
```

Set a dynamic main locale:

```php
use App\Models\Setting;

FilamentAstrotomicPlugin::make()
    ->mainLocale(fn () => Setting::where('key', 'default_locale')->first()?->value ?? 'en')
```

### Customizing the Available Locales

By default, available locales are loaded from `config/translatable.php`.

You can override the locale list when registering the plugin. This is useful when locales are stored in a database, tenant settings, or any other runtime configuration.

```php
FilamentAstrotomicPlugin::make()
    ->locales(['ar', 'en', 'fr'])
```

## Basic Usage

### 1. Preparing Your Model

Make your Eloquent model translatable as described in the `astrotomic/laravel-translatable` documentation.

```php
// app/Models/Post.php

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements TranslatableContract
{
    use Translatable;

    public array $translatedAttributes = [
        'title',
        'content',
    ];

    protected $fillable = [
        'author_id',
    ];
}
```

### 2. Preparing Your Resource

Apply the `ResourceTranslatable` trait to your Filament resource class:

```php
// app/Filament/Resources/PostResource.php

use Filament\Resources\Resource;
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;

class PostResource extends Resource
{
    use ResourceTranslatable;

    // ...
}
```

### 3. Making Resource Pages Translatable

Apply the corresponding translatable trait to each resource page.

#### List Page

```php
// app/Filament/Resources/PostResource/Pages/ListPosts.php

use Filament\Resources\Pages\ListRecords;
use Fnxsoftware\FilamentAstrotomic\Resources\Pages\ListTranslatable;

class ListPosts extends ListRecords
{
    use ListTranslatable;

    // ...
}
```

#### Create Page

```php
// app/Filament/Resources/PostResource/Pages/CreatePost.php

use Filament\Resources\Pages\CreateRecord;
use Fnxsoftware\FilamentAstrotomic\Resources\Pages\CreateTranslatable;

class CreatePost extends CreateRecord
{
    use CreateTranslatable;

    // ...
}
```

#### Edit Page

```php
// app/Filament/Resources/PostResource/Pages/EditPost.php

use Filament\Resources\Pages\EditRecord;
use Fnxsoftware\FilamentAstrotomic\Resources\Pages\EditTranslatable;

class EditPost extends EditRecord
{
    use EditTranslatable;

    // ...
}
```

#### View Page

```php
// app/Filament/Resources/PostResource/Pages/ViewPost.php

use Filament\Resources\Pages\ViewRecord;
use Fnxsoftware\FilamentAstrotomic\Resources\Pages\ViewTranslatable;

class ViewPost extends ViewRecord
{
    use ViewTranslatable;

    // ...
}
```

## Form Components

### `TranslatableTabs` for Localized Fields

To manage translations for your model's attributes, use the `TranslatableTabs` component. It automatically creates a tab for each locale.

```php
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;

TranslatableTabs::make()
    ->localeTabSchema(fn (TranslatableTab $tab): array => [
        TextInput::make($tab->makeName('title'))
            ->required($tab->isMainLocale()),

        RichEditor::make($tab->makeName('content')),
    ])
```

<img width="3072" height="892" alt="CleanShot 2025-09-29 at 12 03 33@2x" src="https://github.com/user-attachments/assets/15c1f035-4997-476f-b249-16da8c007e48" />

#### Customizing Field Labels

Use `makePrefixLabel()` and `makeSuffixLabel()` on the `TranslatableTab` object to add the locale name to labels.

```php
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;

TranslatableTabs::make()
    ->localeTabSchema(fn (TranslatableTab $tab): array => [
        TextInput::make($tab->makeName('title'))
            ->label($tab->makePrefixLabel('Title')),

        Textarea::make($tab->makeName('description'))
            ->label($tab->makeSuffixLabel('Description')),
    ])
```

#### Custom Locales Per `TranslatableTabs` Instance

By default, `TranslatableTabs` uses the locales configured for the plugin, or the `translatable.locales` config.

You can override locales for a specific form section:

```php
use Filament\Forms\Components\TextInput;
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;

TranslatableTabs::make()
    ->customLocales(['ar', 'en', 'fr', 'pt'])
    ->localeTabSchema(fn (TranslatableTab $tab): array => [
        TextInput::make($tab->makeName('label'))
            ->required($tab->isMainLocale()),
    ])
```

## `TranslatableSelect` for Relationships

The standard `Select::relationship()` component does not work reliably with `astrotomic/laravel-translatable` when the display attribute is translated.

For example, if the related model has a translated `name` attribute, the `name` column usually lives in the translation table, not the base table. This means a normal relationship select may try to select a missing column such as `countries.name`.

This package provides a `TranslatableSelect` component to correctly load, search, and display options from a translatable `BelongsTo` relationship.

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Tables\TranslatableSelect;

TranslatableSelect::make('country_id')
    ->translatableRelationship('country', 'name')
    ->searchable()
    ->preload()
    ->label('Country')
    ->required();
```

This will display the translated country name in the current locale and search through the translated `name` attribute.

### Custom Translated Option Labels

Sometimes the option label is not a single translated attribute. For example, a `Person` label may need to combine several translated fields:

```text
first_name + father_name + last_name
```

Use `translatableLabelUsing()` to customize the displayed label.

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Tables\TranslatableSelect;
use Modules\People\Models\Person;

TranslatableSelect::make('person_id')
    ->label('Person')
    ->translatableRelationship('person', 'first_name')
    ->translatableLabelUsing(fn (Person $record, string $locale): string => collect([
        $record->translate($locale)?->first_name,
        $record->translate($locale)?->father_name,
        $record->translate($locale)?->last_name,
    ])->filter()->implode(' ') ?: "#{$record->getKey()}")
    ->searchable()
    ->preload()
    ->required();
```

### Searching Multiple Translated Attributes

When using a custom label, you may also define multiple translated attributes to search.

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Tables\TranslatableSelect;
use Modules\People\Models\Person;

TranslatableSelect::make('person_id')
    ->label('Person')
    ->translatableRelationship('person', 'first_name')
    ->translatableLabelUsing(fn (Person $record, string $locale): string => collect([
        $record->translate($locale)?->first_name,
        $record->translate($locale)?->father_name,
        $record->translate($locale)?->last_name,
    ])->filter()->implode(' ') ?: "#{$record->getKey()}")
    ->translatableSearchAttributes([
        'first_name',
        'father_name',
        'last_name',
    ])
    ->searchable()
    ->preload()
    ->required();
```

### Filtering or Ordering the Related Query

You may pass a query modifier as the third argument to `translatableRelationship()`.

```php
TranslatableSelect::make('department_id')
    ->label('Department')
    ->translatableRelationship(
        relationship: 'department',
        titleAttribute: 'name',
        modifyQueryUsing: fn ($query) => $query
            ->where('active', true)
            ->orderBy('order'),
    )
    ->searchable()
    ->preload();
```

### Available Methods

```php
TranslatableSelect::make('person_id')
    ->translatableRelationship('person', 'first_name')
    ->translatableLabelUsing(fn ($record, string $locale): string => '...')
    ->translatableSearchAttributes([
        'first_name',
        'last_name',
    ]);
```

| Method | Description |
| --- | --- |
| `translatableRelationship(string $relationship, string $titleAttribute, ?Closure $modifyQueryUsing = null)` | Defines the related model relationship and fallback translated display attribute. |
| `translatableLabelUsing(?Closure $callback)` | Customizes the option label using the related record and active locale. |
| `translatableSearchAttributes(array $attributes)` | Defines one or more translated or base-table attributes to search. |

## Displaying Translated Content Reactively

This package provides a seamless way to view translated content on List and View pages using a `LocaleSwitcher` action that works with dedicated table columns and infolist entries.

### 1. Add the `LocaleSwitcher` Action

Add the `LocaleSwitcher` to the header actions of your List and View pages.

```php
// In your List page, for example ListPosts.php

use Filament\Actions\CreateAction;
use Fnxsoftware\FilamentAstrotomic\Actions\LocaleSwitcher;

protected function getHeaderActions(): array
{
    return [
        CreateAction::make(),
        LocaleSwitcher::make(),
    ];
}
```

<img width="3072" height="898" alt="CleanShot 2025-09-29 at 12 04 48@2x" src="https://github.com/user-attachments/assets/a59f0fa7-4709-483e-bda6-3e3a9604ad0b" />

### 2. Use `TranslatableColumn` in Tables

The `TranslatableColumn` automatically displays the translation for the selected locale and provides out-of-the-box search and sort functionality.

```php
use Filament\Tables\Table;
use Fnxsoftware\FilamentAstrotomic\Tables\Columns\TranslatableColumn;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TranslatableColumn::make('title')
                ->searchable()
                ->sortable(),
        ]);
}
```

### 3. Use `TranslatableEntry` in Infolists

Use `TranslatableEntry` in infolists to display translated content that reacts to the `LocaleSwitcher`.

```php
use Filament\Infolists\Infolist;
use Fnxsoftware\FilamentAstrotomic\Infolists\Components\TranslatableEntry;

public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            TranslatableEntry::make('title'),
            TranslatableEntry::make('content'),
        ]);
}
```

## Single Locale Optimization & Customization

### Automatic Grid View

When your application or a specific tenant only has one locale active, `TranslatableTabs` automatically switches its layout to a standard grid.

This removes the unnecessary tab bar and renders fields directly. It gives a cleaner UI for single-language contexts without requiring code changes.

### Forcing Tabs Layout

If you prefer to always show language tabs, even when only one locale is available, you can force the layout globally or per component.

#### Global Configuration

```php
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(
            FilamentAstrotomicPlugin::make()
                ->force()
        );
}
```

#### Per-Component Configuration

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;

TranslatableTabs::make('translations')
    ->force()
    ->localeTabSchema(fn (TranslatableTab $tab): array => [
        // ...
    ])
```

## Custom Locales for Switcher

You can define specific locales for the `LocaleSwitcher` action, overriding global or tenant configuration.

```php
use Fnxsoftware\FilamentAstrotomic\Actions\LocaleSwitcher;

LocaleSwitcher::make()
    ->locales(['en', 'fr']);

LocaleSwitcher::make()
    ->locales([
        'en' => 'English (US)',
        'fr' => 'Français (France)',
    ]);
```

## Advanced Usage

### Custom Locales Per Resource

Override `getTranslatableLocales()` in your resource to specify a different set of locales than the global configuration.

```php
public static function getTranslatableLocales(): array
{
    return ['en', 'fr'];
}
```

### Modal Forms

To use translatable fields inside modal actions, such as an `EditAction` on a table row, you must correctly mutate the data.

```php
use App\Models\Post;
use Filament\Tables\Actions\EditAction;

->actions([
    EditAction::make()
        ->mutateRecordDataUsing(function (Post $record, array $data): array {
            return static::mutateTranslatableData($record, $data);
        }),
])
```

### Nested Relationship Support

`TranslatableColumn` and `TranslatableEntry` support displaying translated attributes on nested relationships using dot notation.

Search support is available for relationship paths handled by the component.

Sorting support is currently limited and should be considered supported for direct attributes and simple relationship cases only.

#### `TranslatableColumn` with a Relationship

```php
use Fnxsoftware\FilamentAstrotomic\Tables\Columns\TranslatableColumn;

TranslatableColumn::make('country.name')
    ->label('Country')
    ->searchable()
    ->sortable();
```

#### `TranslatableEntry` with a Relationship

```php
use Fnxsoftware\FilamentAstrotomic\Infolists\Components\TranslatableEntry;

TranslatableEntry::make('country.name')
    ->label('Country');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Credits

- **Original Author:** [Oleksandr Moik (cactus-galaxy)](https://github.com/oleksandr-moik)
- **Filament v4 Fork:** [Alary Dorian (Doriiaan)](https://github.com/Doriiaan)
- **Current Maintainer:** [Fnx-Software](https://github.com/fnx-software)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

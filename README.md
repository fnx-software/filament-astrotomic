# Filament Astrotomic Translations

[![Total Downloads](https://img.shields.io/packagist/dt/fnx-software/filament-astrotomic)](https://packagist.org/packages/fnx-software/filament-astrotomic)

This package is an extension for **Filament v5** and [laravel-translatable](https://docs.astrotomic.info/laravel-translatable) to easily manage multilingual content in your admin panel.

For **Filament v4**, use an older compatible release/tag.

This is an enhanced fork of [doriiaan/filament-astrotomic](https://github.com/Doriiaan/filament-astrotomic) and the original [cactus-galaxy/filament-astrotomic](https://github.com/CactusGalaxy/FilamentAstrotomic), updated for Filament 5 and introducing powerful new features like a reactive `LocaleSwitcher` and dedicated components for displaying translated content.

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
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentAstrotomicPlugin::make(),
        ]);
}
```

### Customizing the Main Locale (Optional)

By default, the main locale is taken from your `config/translatable.php` file. You can override this dynamically when registering the plugin.

**Set a static main locale:**
```php
FilamentAstrotomicPlugin::make()
    ->mainLocale('ar')
```

**Set a dynamic main locale (e.g., from database settings):**
```php
use App\Models\Setting;

FilamentAstrotomicPlugin::make()
    ->mainLocale(fn () => Setting::where('key', 'default_locale')->first()?->value ?? 'en')
```
### Customizing the Available Locales (Optional)

By default, the available locales are loaded from your `config/translatable.php` file.

You can override the locale list when registering the plugin, which is useful when your locales are stored in the database (e.g., per-tenant settings).

**Set a static locales list:**
```php
FilamentAstrotomicPlugin::make()
    ->locales(['ar', 'en', 'fr'])
```

## Basic Usage

### 1. Preparing Your Model

Make your Eloquent model translatable as described in the [laravel-translatable documentation](https://docs.astrotomic.info/laravel-translatable/installation#models).

```php
// app/Models/Post.php
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Post extends Model implements TranslatableContract
{
    use Translatable;

    public array $translatedAttributes = ['title', 'content'];
    protected $fillable = ['author_id'];
}
```

### 2. Preparing Your Resource

Apply the `ResourceTranslatable` trait to your Filament resource class:

```php
// app/Filament/Resources/PostResource.php
use Fnxsoftware\FilamentAstrotomic\Resources\Concerns\ResourceTranslatable;
use Filament\Resources\Resource;

class PostResource extends Resource
{
    use ResourceTranslatable;

    // ...
}
```

### 3. Making Resource Pages Translatable

You must also apply the corresponding translatable trait to each of your resource's pages.

**List Page:**
```php
// app/Filament/Resources/PostResource/Pages/ListPosts.php
use Fnxsoftware\FilamentAstrotomic\Resources\Pages\ListTranslatable;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use ListTranslatable;
    // ...
}
```

**Create Page:**
```php
// app/Filament/Resources/PostResource/Pages/CreatePost.php
use Fnxsoftware\FilamentAstrotomic\Resources\Pages\CreateTranslatable;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    use CreateTranslatable;
    // ...
}
```

**Edit Page:**
```php
// app/Filament/Resources/PostResource/Pages/EditPost.php
use Fnxsoftware\FilamentAstrotomic\Resources\Pages\EditTranslatable;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use EditTranslatable;
    // ...
}
```

**View Page:**
```php
// app/Filament/Resources/PostResource/Pages/ViewPost.php
use Fnxsoftware\FilamentAstrotomic\Resources\Pages\ViewTranslatable;
use Filament\Resources\Pages\ViewRecord;

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
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;

public static function form(Form $form): Form
{
    return $form->schema([
        TranslatableTabs::make()
            ->localeTabSchema(fn (TranslatableTab $tab) => [
                TextInput::make($tab->makeName('title'))
                    ->required($tab->isMainLocale()), // Required only for the main language

                RichEditor::make($tab->makeName('content')),
            ])
    ]);
}
```
<img width="3072" height="892" alt="CleanShot 2025-09-29 at 12 03 33@2x" src="https://github.com/user-attachments/assets/15c1f035-4997-476f-b249-16da8c007e48" />

#### Customizing Field Labels

To make it clearer which language a field belongs to, you can use the `makePrefixLabel()` and `makeSuffixLabel()` methods on the `TranslatableTab` object to automatically add the locale name to your field labels.

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

TranslatableTabs::make()
    ->localeTabSchema(fn (TranslatableTab $tab) => [
        TextInput::make($tab->makeName('title'))
            // Renders label as "Title (English)"
            ->label($tab->makePrefixLabel('Title')),

        Textarea::make($tab->makeName('description'))
            // Renders label as "(English) Description"
            ->label($tab->makeSuffixLabel('Description')),
    ])
```
#### Custom Locales Per `TranslatableTabs` Instance

By default, `TranslatableTabs` will use the locales configured for the plugin (or the `translatable.locales` config).

If you need to override the locales for a specific form (or even for a single set of fields), you may pass a custom list directly to the component:

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;
use Fnxsoftware\FilamentAstrotomic\TranslatableTab;
use Filament\Forms\Components\TextInput;

TranslatableTabs::make()
    ->customLocales(['ar', 'en', 'fr', 'pt'])
    ->localeTabSchema(fn (TranslatableTab $tab) => [
        TextInput::make($tab->makeName('label'))
            ->required($tab->isMainLocale()),
    ])
```

### `TranslatableSelect` for Relationships

The standard `Select::relationship()` component does not work with `astrotomic/laravel-translatable`. This package provides a `TranslatableSelect` component to correctly load, search, and display options from a translatable `BelongsTo` relationship.

```php
use Fnxsoftware\FilamentAstrotomic\Forms\Components\TranslatableSelect;
use Filament\Forms\Form;

public static function form(Form $form): Form
{
    return $form->schema([
        // ... other fields
        TranslatableSelect::make('country_id')
            ->translatableRelationship('country', 'name')
            ->searchable()
            ->preload()
            ->label('Country')
            ->required(),
    ]);
}
```
This will correctly display the country names in the current locale, and the search functionality will also work on the translated names.


## Displaying Translated Content Reactively

This package provides a seamless way to view translated content on your List and View pages using a `LocaleSwitcher` action that works with dedicated table columns and infolist entries.

### 1. Add the `LocaleSwitcher` Action

Add the `LocaleSwitcher` to the header actions of your List and View pages.

```php
// In your List page (e.g., ListPosts.php)
use Fnxsoftware\FilamentAstrotomic\Actions\LocaleSwitcher;
use Filament\Actions\CreateAction;

protected function getHeaderActions(): array
{
    return [
        CreateAction::make(),
        LocaleSwitcher::make(), // Add this
    ];
}
```
<img width="3072" height="898" alt="CleanShot 2025-09-29 at 12 04 48@2x" src="https://github.com/user-attachments/assets/a59f0fa7-4709-483e-bda6-3e3a9604ad0b" />


### 2. Use `TranslatableColumn` in Tables

The `TranslatableColumn` automatically displays the translation for the selected locale and provides out-of-the-box search and sort functionality.

```php
// In your Table definition
use Fnxsoftware\FilamentAstrotomic\Tables\Columns\TranslatableColumn;
use Filament\Tables\Table;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TranslatableColumn::make('title')
                ->searchable()
                ->sortable(),
            // ... other columns
        ]);
}
```

### 3. Use `TranslatableEntry` in Infolists

Similarly, use `TranslatableEntry` in your infolists to display translated content that reacts to the `LocaleSwitcher`.

```php
// In your Infolist definition
use Fnxsoftware\FilamentAstrotomic\Infolists\Components\TranslatableEntry;
use Filament\Infolists\Infolist;

public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            TranslatableEntry::make('title'),
            TranslatableEntry::make('content'),
            // ... other entries
        ]);
}
```

## 🚀 Single Locale Optimization & Customization

### Automatic Grid View
When your application or a specific tenant only has **one locale** active, `TranslatableTabs` will automatically switch its layout to a standard **Grid**.

This removes the unnecessary tab bar, rendering the fields directly. This provides a cleaner UI for single-language contexts without requiring code changes.

### Forcing Tabs Layout
If you prefer to always show the language tabs (even when only one locale is available), you can force the layout using one of two methods:

#### 1. Global Configuration
Force tabs globally for all `TranslatableTabs` components in your `AdminPanelProvider`:

```php
use Fnxsoftware\FilamentAstrotomic\FilamentAstrotomicPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(
            FilamentAstrotomicPlugin::make()
                ->force() // Always show tabs
        );
}
```

#### 2. Per-Component Configuration
Force tabs on a specific component:

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\TranslatableTabs;

TranslatableTabs::make('translations')
    ->force() // Always show tabs for this specific component
    ->localeTabSchema(fn (TranslatableTab $tab) => [
        // ...
    ])
```

---

### Custom Locales for Switcher
You can now define specific locales for the `LocaleSwitcher` action, overriding the global or tenant configurations. This is useful if specific resources or pages only support a subset of languages.

```php
use Fnxsoftware\FilamentAstrotomic\Actions\LocaleSwitcher;

// Pass a list of locale codes (labels generated automatically)
LocaleSwitcher::make()
    ->locales(['en', 'fr']);

// Or pass custom labels
LocaleSwitcher::make()
    ->locales([
        'en' => 'English (US)',
        'fr' => 'Français (France)',
    ]);
```

## Advanced Usage

### Custom Locales Per-Resource

Override the `getTranslatableLocales()` method in your resource to specify a different set of locales than the global configuration.

```php
public static function getTranslatableLocales(): array
{
    return ['en', 'fr'];
}
```

### Modal Forms

To use translatable fields inside modal actions (like an `EditAction` on a table row), you must correctly mutate the data.

```php
use App\Models\Post;
use Filament\Tables\Actions\EditAction;

->actions([
    EditAction::make()->mutateRecordDataUsing(function (Post $record, array $data) {
        return static::mutateTranslatableData($record, $data);
    }),
])
```

### Nested Relationship Support

`TranslatableColumn` and `TranslatableEntry` support displaying translated attributes on nested relationships using dot notation.

Search support is available for relationship paths handled by the component.

Sorting support is currently limited and should be considered supported for direct attributes and simple relationship cases only.

**`TranslatableColumn` with a relationship:**

The column will display the country's name, and the search and sort functionality will correctly filter and order the `governorates` table based on the name of the related country.

```php
// In your GovernorateResource table
TranslatableColumn::make('country.name')
    ->label('Country')
    ->searchable()
    ->sortable(),
```

**`TranslatableEntry` with a relationship:**

The entry will display the translated name of the related country on the governorate's view page.

```php
// In your GovernorateResource infolist
TranslatableEntry::make('country.name')
    ->label('Country'),
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

-   **Original Author:** [Oleksandr Moik (cactus-galaxy)](https://github.com/oleksandr-moik)
-   **Filament v4 Fork:** [Alary Dorian (Doriiaan)](https://github.com/Doriiaan)
-   **Current Maintainer:** [Fnx-Software](https://github.com/fnx-software)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

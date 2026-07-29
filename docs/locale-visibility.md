# Configurable locale visibility

Use `LocaleAwareTranslatableTabs` when locales are configured dynamically, such as per tenant or per school.

## Without a locale picker

All custom locales are visible automatically:

```php
use Fnxsoftware\FilamentAstrotomic\Schemas\Components\LocaleAwareTranslatableTabs;

LocaleAwareTranslatableTabs::make()
    ->customLocales([
        'en' => 'English',
        'ar' => 'Arabic',
        'fr' => 'French',
    ])
    ->localeTabSchema(/* ... */);
```

## With a locale picker

Only the main locale and the locales passed to `displayCustomLocales()` are visible initially. Existing translation locales are also preserved.

```php
LocaleAwareTranslatableTabs::make()
    ->customLocales([
        'en' => 'English',
        'ar' => 'Arabic',
        'fr' => 'French',
    ])
    ->displayCustomLocales(['ar'])
    ->hasSuffixLocalePicker()
    ->localeTabSchema(/* ... */);
```

The picker can then add or remove the remaining configured locales.

`displayCustomLocales()` accepts a closure:

```php
->displayCustomLocales(fn (): array => ['ar', 'fr'])
```

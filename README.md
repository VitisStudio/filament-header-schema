# Filament Header Schema

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vitisstudio/filament-header-schema.svg?style=flat-square)](https://packagist.org/packages/vitisstudio/filament-header-schema)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vitisstudio/filament-header-schema/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vitisstudio/filament-header-schema/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/vitisstudio/filament-header-schema/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/vitisstudio/filament-header-schema/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vitisstudio/filament-header-schema.svg?style=flat-square)](https://packagist.org/packages/vitisstudio/filament-header-schema)

Build the header of a Filament page with a schema instead of a Blade view.

Filament gives you `getHeading()` and `getSubheading()` for plain text, and `getHeader()` for everything else — which means dropping to a Blade view the moment you want an avatar next to the title, a status badge beside it, and a couple of totals pushed to the right. This package adds a `headerSchema()` method that sits alongside `form()` and `infolist()`, so a rich header is written the same way as the rest of the page.

```php
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;
use VitisStudio\FilamentHeaderSchema\Components\HeaderSection;
use VitisStudio\FilamentHeaderSchema\Components\Heading;
use VitisStudio\FilamentHeaderSchema\Components\Subheading;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;

class ViewOrder extends ViewRecord
{
    use HasHeaderSchema;

    protected static string $resource = OrderResource::class;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            HeaderSection::make([
                Heading::make('customer_name'),
                Flex::make([
                    TextEntry::make('status')->badge()->hiddenLabel(),
                    TextEntry::make('reference')->hiddenLabel(),
                ]),
                Subheading::make('summary'),
            ])
                ->leading(ImageEntry::make('customer_avatar')->circular()->hiddenLabel())
                ->trailing(TextEntry::make('total')->money()->hiddenLabel()),
        ]);
    }
}
```

Everything you can put in an infolist works, because a header schema *is* a schema — entries, layouts, actions, visibility rules, the lot.

## Requirements

- PHP 8.2+
- Laravel 11.28, 12 or 13
- Filament 5.7+

## Installation

```bash
composer require vitisstudio/filament-header-schema
```

That's the whole install. There is no asset to publish and no theme change to make: the package's styles are written against Filament's own design tokens and inlined into the page head, so dark mode and custom panel colours work out of the box.

## Usage

### Opting a page in

Add the trait to any resource View, Edit or List page and define `headerSchema()`:

```php
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;

class ViewOrder extends ViewRecord
{
    use HasHeaderSchema;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Heading::make('reference'),
        ]);
    }
}
```

The schema replaces the page's heading and subheading. Breadcrumbs and the header actions row are untouched, so `getHeaderActions()` keeps working exactly as before.

### Falling back to the old way

The trait is additive. A page with no `headerSchema()` method — or one whose schema resolves to no components — renders Filament's native heading, unchanged. You can apply the trait to a base page class and opt individual pages in over time.

To take the header over completely, define `getHeader()` on the page as you always would. A method on the page wins over one inherited from a trait, so your Blade view is used and the schema is ignored:

```php
public function getHeader(): ?View
{
    return view('orders.header', ['record' => $this->getRecord()]);
}
```

### Reading data

Components read state exactly like infolist entries. On View and Edit pages the schema is bound to the record, so a component name is an attribute path:

```php
Heading::make('reference')          // $record->reference
Subheading::make('customer.name')   // $record->customer->name
```

List pages have no record. Use `->state()` for a literal or a closure, which receives the usual Filament arguments including `$livewire`:

```php
Heading::make('title')->state('Orders'),
Subheading::make('count')->state(fn ($livewire) => $livewire->getModel()::count().' total'),
```

If a page should bind to some other record — a tenant, a parent model, a settings singleton — override `getHeaderSchemaRecord()`:

```php
public function getHeaderSchemaRecord(): ?Model
{
    return Filament::getTenant();
}
```

## Components

Infolist entries and schema layouts all work as-is. These three fill the gaps Filament has no component for.

### `Heading`

A real `<h1>`–`<h6>` element, sized and weighted to stand out.

```php
Heading::make('reference')
    ->level(2)                  // 1 by default
    ->size('2xl')               // xs, sm, md, lg, xl, 2xl, 3xl
    ->weight(FontWeight::Bold)
    ->color('danger')
    ->icon(Heroicon::Star)
    ->iconPosition(IconPosition::After)
    ->placeholder('Untitled')   // shown when the state is blank
```

Level 1 matches Filament's native page heading exactly, so opting a page in doesn't change how it looks until you want it to. Each level has a sensible default size; `->size()` overrides it.

Headings render bare — no entry wrapper, so no label, hint or helper text. The heading text *is* the label.

### `Subheading`

The muted supporting line, as a `<p>`. Same modifiers as `Heading`, minus `->level()`, and defaults to the `lg` size Filament's native subheading uses.

```php
Subheading::make('summary')->size('sm')->color('gray')
```

### `HeaderSection`

The flexbox layout a rich header usually wants, in three slots:

```php
HeaderSection::make([
    // main slot — stacks vertically
    Heading::make('name'),
    Subheading::make('email'),
])
    ->leading(ImageEntry::make('avatar')->circular()->hiddenLabel())
    ->trailing([
        TextEntry::make('orders_count')->hiddenLabel(),
        TextEntry::make('lifetime_value')->money()->hiddenLabel(),
    ])
```

Slots with no components are not rendered. The three slots stack in a column on small screens and become a row from the `sm` breakpoint; `->from('md')` moves that, and `->verticallyAlignStart()` / `->verticallyAlignCenter()` / `->verticallyAlignEnd()` control cross-axis alignment. Nest a `Flex` inside a slot for anything that should sit side by side, such as a row of badges.

### Labels

Infolist entries render their label by default, which is rarely what a header wants. Call `->hiddenLabel()` on entries you place in a header schema. `Heading` and `Subheading` never render one.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for what has changed recently.

## Credits

- [Dan Poblete](https://github.com/acepoblete)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

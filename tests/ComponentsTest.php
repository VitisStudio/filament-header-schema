<?php

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Icons\Heroicon;
use Livewire\Livewire;
use VitisStudio\FilamentHeaderSchema\Components\HeaderSection;
use VitisStudio\FilamentHeaderSchema\Components\Heading;
use VitisStudio\FilamentHeaderSchema\Components\Subheading;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Models\Order;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource\Pages\ViewOrder;

/**
 * Renders components through a real page so they resolve state from a record
 * exactly as they would in an application.
 *
 * @param  array<mixed>  $components
 */
function renderHeader(array $components, ?Order $record = null): string
{
    $record ??= order();

    /** @var ViewOrder $livewire */
    $livewire = Livewire::test(ViewOrder::class, ['record' => $record->getKey()])->instance();

    return Schema::make($livewire)
        ->record($record)
        ->components($components)
        ->toHtml();
}

it('renders a heading at the requested level', function (int $level) {
    expect(renderHeader([Heading::make('reference')->level($level)]))
        ->toContain("<h{$level}")
        ->toContain("fi-hs-heading-level-{$level}")
        ->toContain('INV-1024');
})->with([1, 2, 3, 4, 5, 6]);

it('defaults a heading to level 1', function () {
    expect(renderHeader([Heading::make('reference')]))
        ->toContain('<h1')
        ->toContain('fi-hs-heading-level-1');
});

it('rejects a heading level outside 1 to 6', function () {
    Heading::make('reference')->level(7);
})->throws(InvalidArgumentException::class, 'Heading level [7] is out of range');

it('rejects a heading level a closure resolves out of range', function () {
    // Filament's schema renderer opens an output buffer it never closes when a
    // component throws mid-render, so unwind to where we started.
    $bufferLevel = ob_get_level();

    try {
        renderHeader([Heading::make('reference')->level(fn (): int => 0)]);
    } finally {
        while (ob_get_level() > $bufferLevel) {
            ob_end_clean();
        }
    }
})->throws(InvalidArgumentException::class, 'Heading level [0] is out of range');

it('reads heading state from the record', function () {
    expect(renderHeader([Heading::make('customer_name')]))->toContain('ACME Corp');
});

it('accepts a literal heading through state', function () {
    expect(renderHeader([Heading::make('title')->state('All orders')]))->toContain('All orders');
});

it('accepts a closure for heading state', function () {
    $html = renderHeader([
        Heading::make('title')->state(fn ($record): string => strtoupper($record->status)),
    ]);

    expect($html)->toContain('PAID');
});

it('renders nothing when heading state is blank and there is no placeholder', function () {
    $html = renderHeader([Heading::make('customer_name')], order(['customer_name' => null]));

    expect($html)->not->toContain('fi-hs-heading');
});

it('falls back to the placeholder when heading state is blank', function () {
    $html = renderHeader(
        [Heading::make('customer_name')->placeholder('No customer')],
        order(['customer_name' => null]),
    );

    expect($html)->toContain('No customer');
});

it('applies size, weight and color modifiers to a heading', function () {
    $html = renderHeader([
        Heading::make('reference')
            ->size('2xl')
            ->weight(FontWeight::SemiBold)
            ->color('danger'),
    ]);

    expect($html)
        ->toContain('fi-hs-size-2xl')
        ->toContain('fi-font-semibold')
        ->toContain('fi-color-danger');
});

it('renders a heading icon on either side', function (IconPosition $position, string $expectedOrder) {
    $html = renderHeader([
        Heading::make('reference')->icon(Heroicon::Star)->iconPosition($position),
    ]);

    expect($html)->toMatch("/{$expectedOrder}/s");
})->with([
    [IconPosition::Before, 'fi-icon.*INV-1024'],
    [IconPosition::After, 'INV-1024.*fi-icon'],
]);

it('only makes a heading a flex container when it has an icon', function () {
    expect(renderHeader([Heading::make('reference')]))
        ->not->toContain('fi-hs-has-icon');

    expect(renderHeader([Heading::make('reference')->icon(Heroicon::Star)]))
        ->toContain('fi-hs-has-icon');
});

it('applies an alignment class to a heading and a subheading', function () {
    expect(renderHeader([Heading::make('reference')->alignEnd()]))
        ->toContain('fi-align-end');

    expect(renderHeader([Subheading::make('customer_name')->alignCenter()]))
        ->toContain('fi-align-center');
});

it('renders a subheading as a paragraph', function () {
    expect(renderHeader([Subheading::make('customer_name')]))
        ->toContain('<p')
        ->toContain('fi-hs-subheading')
        ->toContain('ACME Corp');
});

it('applies size and color modifiers to a subheading', function () {
    $html = renderHeader([
        Subheading::make('customer_name')->size('sm')->color('primary'),
    ]);

    expect($html)
        ->toContain('fi-hs-size-sm')
        ->toContain('fi-color-primary');
});

it('renders header section slots in leading, main, trailing order', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->leading(TextEntry::make('status')->hiddenLabel())
            ->trailing(TextEntry::make('customer_name')->hiddenLabel()),
    ]);

    expect($html)
        ->toContain('fi-hs-section')
        ->toContain('fi-hs-section-leading')
        ->toContain('fi-hs-section-main')
        ->toContain('fi-hs-section-trailing');

    expect(strpos($html, 'fi-hs-section-leading'))
        ->toBeLessThan(strpos($html, 'fi-hs-section-main'));

    expect(strpos($html, 'fi-hs-section-main'))
        ->toBeLessThan(strpos($html, 'fi-hs-section-trailing'));
});

it('omits header section slots that have no components', function () {
    $html = renderHeader([HeaderSection::make([Heading::make('reference')])]);

    expect($html)
        ->toContain('fi-hs-section-main')
        ->not->toContain('fi-hs-section-leading')
        ->not->toContain('fi-hs-section-trailing');
});

it('grows the main slot by default, holding the trailing slot at the far edge', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->trailing(TextEntry::make('customer_name')->hiddenLabel()),
    ]);

    expect($html)->toContain('fi-hs-section-main fi-growable');
});

it('lets the main slot hug its content when growing is turned off', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->grow(false)
            ->trailing(TextEntry::make('customer_name')->hiddenLabel()),
    ]);

    expect($html)
        ->toContain('fi-hs-section-main')
        ->not->toContain('fi-hs-section-main fi-growable');
});

it('accepts a closure for main slot growing', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->grow(fn (): bool => false),
    ]);

    expect($html)->not->toContain('fi-growable');
});

it('stacks a header section until the sm breakpoint by default', function () {
    expect(renderHeader([HeaderSection::make([Heading::make('reference')])]))
        ->toContain('fi-from-sm')
        ->toContain('fi-vertical-align-center');
});

it('accepts a different breakpoint for a header section', function () {
    expect(renderHeader([HeaderSection::make([Heading::make('reference')])->from('lg')]))
        ->toContain('fi-from-lg');
});

it('leaves the slots to follow the section alignment by default', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->leading(TextEntry::make('status')->hiddenLabel())
            ->trailing(TextEntry::make('customer_name')->hiddenLabel()),
    ]);

    expect($html)
        ->toContain('class="fi-hs-section-leading"')
        ->toContain('class="fi-hs-section-trailing"');
});

it('aligns the leading and trailing slots independently of the section', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->leadingVerticallyAlignStart()
            ->trailingVerticallyAlignEnd()
            ->leading(TextEntry::make('status')->hiddenLabel())
            ->trailing(TextEntry::make('customer_name')->hiddenLabel()),
    ]);

    expect($html)
        ->toContain('fi-hs-section-leading fi-vertical-align-start')
        ->toContain('fi-hs-section-trailing fi-vertical-align-end')
        // The section keeps its own alignment for the slots that said nothing.
        ->toContain('fi-vertical-align-center');
});

it('accepts an enum, a string and a closure for slot alignment', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->leadingVerticalAlignment(VerticalAlignment::End)
            ->trailingVerticalAlignment(fn (): string => 'start')
            ->leading(TextEntry::make('status')->hiddenLabel())
            ->trailing(TextEntry::make('customer_name')->hiddenLabel()),
    ]);

    expect($html)
        ->toContain('fi-hs-section-leading fi-vertical-align-end')
        ->toContain('fi-hs-section-trailing fi-vertical-align-start');
});

it('aligns the main slot independently of the section', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->mainVerticallyAlignStart(),
    ]);

    expect($html)->toContain('fi-hs-section-main fi-growable fi-vertical-align-start');
});

it('takes a slot alignment as the second argument of the method that fills it', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')], VerticalAlignment::Center)
            ->leading(TextEntry::make('status')->hiddenLabel(), VerticalAlignment::Start)
            ->trailing(TextEntry::make('customer_name')->hiddenLabel(), 'end'),
    ]);

    expect($html)
        ->toContain('fi-hs-section-leading fi-vertical-align-start')
        ->toContain('fi-hs-section-main fi-growable fi-vertical-align-center')
        ->toContain('fi-hs-section-trailing fi-vertical-align-end');
});

it('leaves a slot alignment alone when the second argument is omitted', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->leadingVerticallyAlignEnd()
            ->leading(TextEntry::make('status')->hiddenLabel()),
    ]);

    expect($html)->toContain('fi-hs-section-leading fi-vertical-align-end');
});

it('does not align a slot when the condition is false', function () {
    $html = renderHeader([
        HeaderSection::make([Heading::make('reference')])
            ->leadingVerticallyAlignStart(false)
            ->leading(TextEntry::make('status')->hiddenLabel()),
    ]);

    expect($html)->toContain('class="fi-hs-section-leading"');
});

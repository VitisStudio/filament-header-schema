<?php

use Filament\Schemas\Schema;
use Livewire\Livewire;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource\Pages\EditOrder;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource\Pages\ListOrders;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource\Pages\ViewOrder;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\PlainOrderResource\Pages\ListPlainOrders;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\PlainOrderResource\Pages\ViewPlainOrder;

it('renders the header schema on a view page', function () {
    $order = order();

    Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
        ->assertSeeHtml('fi-hs-heading')
        ->assertSee('INV-1024')
        ->assertSee('ACME Corp');
});

it('replaces the native heading but keeps header actions', function () {
    $order = order();

    Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
        ->assertDontSeeHtml('fi-header-heading')
        ->assertSeeHtml('fi-header-actions-ctn')
        ->assertSee('Archive order');
});

it('renders the header schema on an edit page', function () {
    $order = order();

    Livewire::test(EditOrder::class, ['record' => $order->getKey()])
        ->assertSeeHtml('<h2')
        ->assertSeeHtml('fi-hs-heading-level-2')
        ->assertSee('INV-1024');
});

it('renders a header schema on a list page, where there is no record', function () {
    order();
    order(['reference' => 'INV-1025']);

    Livewire::test(ListOrders::class)
        ->assertSee('All orders')
        ->assertSee('2 orders');
});

it('binds the header schema to the record on record pages', function () {
    $order = order();

    $page = new ViewOrder;
    $page->record = $order;

    expect($page->getHeaderSchemaRecord())->toBe($order);
});

it('makes the header schema dense so it spaces like a header, not page content', function () {
    $order = order();

    $page = new ViewOrder;
    $page->record = $order;

    expect($page->getSchema('headerSchema')->isDense())->toBeTrue();
});

it('has no header schema record on a list page', function () {
    expect((new ListOrders)->getHeaderSchemaRecord())->toBeNull();
});

it('falls back to the native heading when no header schema is defined', function () {
    $order = order();

    $page = new ViewPlainOrder;

    expect($page->hasHeaderSchema())->toBeFalse()
        ->and($page->getHeader())->toBeNull();

    Livewire::test(ViewPlainOrder::class, ['record' => $order->getKey()])
        ->assertSeeHtml('fi-header-heading')
        ->assertSee('Native heading')
        ->assertDontSeeHtml('fi-hs-heading');
});

it('lets a page take the header over completely with its own getHeader', function () {
    Livewire::test(ListPlainOrders::class)
        ->assertSee('Hand-written header')
        ->assertDontSee('Schema heading');
});

it('treats an empty header schema as no header schema', function () {
    $page = new class extends ViewOrder
    {
        public function headerSchema(Schema $schema): Schema
        {
            return $schema->components([]);
        }
    };

    expect($page->hasHeaderSchema())->toBeFalse()
        ->and($page->getHeader())->toBeNull();
});

it('inlines its stylesheet into the page head', function () {
    $order = order();

    $this->get("/admin/orders/{$order->getKey()}")
        ->assertSuccessful()
        ->assertSee('.fi-hs-heading', escape: false);
});

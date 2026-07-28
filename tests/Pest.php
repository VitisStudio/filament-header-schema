<?php

use Filament\Facades\Filament;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Models\Order;
use VitisStudio\FilamentHeaderSchema\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => Filament::setCurrentPanel('admin'))
    ->in(__DIR__);

/**
 * @param  array<string, mixed>  $attributes
 */
function order(array $attributes = []): Order
{
    return Order::create([
        'reference' => 'INV-1024',
        'status' => 'paid',
        'customer_name' => 'ACME Corp',
        ...$attributes,
    ]);
}

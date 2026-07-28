<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Models\Order;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\PlainOrderResource\Pages;

/**
 * Pages that use the trait but do not opt into a header schema, covering the
 * fallback and full-takeover paths.
 */
class PlainOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $slug = 'plain-orders';

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlainOrders::route('/'),
            'view' => Pages\ViewPlainOrder::route('/{record}'),
        ];
    }
}

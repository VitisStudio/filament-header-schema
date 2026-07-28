<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Models\Order;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource\Pages;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static bool $shouldSkipAuthorization = true;

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

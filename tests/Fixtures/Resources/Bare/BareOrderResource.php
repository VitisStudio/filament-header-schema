<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare;

use Filament\Resources\Resource;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Models\Order;

/**
 * Pages with no trait applied, so `make:filament-header-schema` has something
 * untouched to work on.
 */
class BareOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $slug = 'bare-orders';

    protected static ?string $recordTitleAttribute = 'reference';

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBareOrders::route('/'),
            'view' => Pages\ViewBareOrder::route('/{record}'),
            'edit' => Pages\EditBareOrder::route('/{record}/edit'),
        ];
    }
}

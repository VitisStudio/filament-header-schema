<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Convention;

use Filament\Resources\Resource;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Models\Order;

/**
 * A resource whose header schema lives in `Schemas/OrderHeader.php` and is never
 * referenced by name, so the trait has to find it by convention.
 */
class ConventionOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $slug = 'convention-orders';

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConventionOrders::route('/'),
            'view' => Pages\ViewConventionOrder::route('/{record}'),
        ];
    }
}

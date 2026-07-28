<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare;

use Filament\Resources\Resource;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Models\Order;

/**
 * Deliberately has no `$recordTitleAttribute`, so the generator has nothing to
 * seed the heading with.
 */
class NoTitleOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $slug = 'no-title-orders';

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNoTitleOrders::route('/'),
        ];
    }
}

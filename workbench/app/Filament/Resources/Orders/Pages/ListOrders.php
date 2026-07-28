<?php

namespace Workbench\App\Filament\Resources\Orders\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use VitisStudio\FilamentHeaderSchema\Components\Heading;
use VitisStudio\FilamentHeaderSchema\Components\Subheading;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use Workbench\App\Enums\OrderStatus;
use Workbench\App\Filament\Resources\Orders\OrderResource;
use Workbench\App\Models\Order;

/**
 * A list page has no record, so both components take their state from a
 * closure instead of an attribute path.
 */
class ListOrders extends ListRecords
{
    use HasHeaderSchema;

    protected static string $resource = OrderResource::class;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Heading::make('title')->state('Orders'),
            Subheading::make('summary')->state(function (): string {
                $total = Order::count();
                $pending = Order::where('status', OrderStatus::Pending)->count();

                return "{$total} orders, {$pending} awaiting payment";
            }),
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

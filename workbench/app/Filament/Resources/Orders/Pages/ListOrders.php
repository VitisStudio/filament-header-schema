<?php

namespace Workbench\App\Filament\Resources\Orders\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use Workbench\App\Filament\Resources\Orders\OrderResource;

/**
 * The same `Schemas/OrderHeader.php` as ViewOrder, reached by the same
 * convention. There is no record here, so the parts of that schema which read
 * one hide themselves and the heading falls back to this page's title.
 */
class ListOrders extends ListRecords
{
    use HasHeaderSchema;

    protected static string $resource = OrderResource::class;

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

<?php

namespace Workbench\App\Filament\Resources\Orders\Pages;

use Filament\Resources\Pages\CreateRecord;
use Workbench\App\Filament\Resources\Orders\OrderResource;

/**
 * Opted out: no trait, so this page keeps Filament's native heading even though
 * the resource has a header schema. `make:filament-header-schema` leaves create
 * pages unselected by default for exactly this reason.
 */
class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}

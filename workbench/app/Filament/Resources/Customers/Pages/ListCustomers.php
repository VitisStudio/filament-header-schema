<?php

namespace Workbench\App\Filament\Resources\Customers\Pages;

use Filament\Resources\Pages\ListRecords;
use Workbench\App\Filament\Resources\Customers\CustomerResource;

/**
 * No trait at all — the untouched Filament baseline.
 */
class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;
}

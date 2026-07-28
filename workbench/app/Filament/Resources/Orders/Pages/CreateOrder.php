<?php

namespace Workbench\App\Filament\Resources\Orders\Pages;

use Filament\Resources\Pages\CreateRecord;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use Workbench\App\Filament\Resources\Orders\OrderResource;

/**
 * The trait is applied but no `headerSchema()` is defined, so this page keeps
 * Filament's native heading — useful for a side-by-side screenshot.
 */
class CreateOrder extends CreateRecord
{
    use HasHeaderSchema;

    protected static string $resource = OrderResource::class;
}

<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Convention\Pages;

use Filament\Resources\Pages\ViewRecord;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Convention\ConventionOrderResource;

/**
 * The trait alone — no `headerSchema()` method. The header comes from
 * `Schemas/OrderHeader.php`.
 */
class ViewConventionOrder extends ViewRecord
{
    use HasHeaderSchema;

    protected static string $resource = ConventionOrderResource::class;
}

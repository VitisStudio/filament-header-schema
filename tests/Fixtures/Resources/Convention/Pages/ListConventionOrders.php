<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Convention\Pages;

use Filament\Resources\Pages\ListRecords;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Convention\ConventionOrderResource;

/**
 * No record to read `reference` from, so the generated default keeps the page's
 * own heading.
 */
class ListConventionOrders extends ListRecords
{
    use HasHeaderSchema;

    protected static string $resource = ConventionOrderResource::class;
}

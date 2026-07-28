<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\Pages;

use Filament\Resources\Pages\ListRecords;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\BareOrderResource;

class ListBareOrders extends ListRecords
{
    protected static string $resource = BareOrderResource::class;
}

<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\Pages;

use Filament\Resources\Pages\ListRecords;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\NoTitleOrderResource;

class ListNoTitleOrders extends ListRecords
{
    protected static string $resource = NoTitleOrderResource::class;
}

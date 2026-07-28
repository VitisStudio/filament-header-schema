<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\Pages;

use Filament\Resources\Pages\ViewRecord;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\BareOrderResource;

/**
 * A docblock the command must leave intact.
 */
class ViewBareOrder extends ViewRecord
{
    protected static string $resource = BareOrderResource::class;

    public function getHeading(): string
    {
        return 'Native heading';
    }
}

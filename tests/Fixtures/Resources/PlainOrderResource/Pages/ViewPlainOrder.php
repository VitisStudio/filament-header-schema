<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\PlainOrderResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\PlainOrderResource;

/**
 * The trait is applied but no `headerSchema()` is defined, so the page keeps
 * Filament's native heading.
 */
class ViewPlainOrder extends ViewRecord
{
    use HasHeaderSchema;

    protected static string $resource = PlainOrderResource::class;

    public function getHeading(): string
    {
        return 'Native heading';
    }
}

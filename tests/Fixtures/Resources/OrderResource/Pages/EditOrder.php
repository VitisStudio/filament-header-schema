<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use VitisStudio\FilamentHeaderSchema\Components\Heading;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource;

class EditOrder extends EditRecord
{
    use HasHeaderSchema;

    protected static string $resource = OrderResource::class;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Heading::make('reference')->level(2),
        ]);
    }
}

<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use VitisStudio\FilamentHeaderSchema\Components\Heading;
use VitisStudio\FilamentHeaderSchema\Components\Subheading;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource;

class ListOrders extends ListRecords
{
    use HasHeaderSchema;

    protected static string $resource = OrderResource::class;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Heading::make('title')->state('All orders'),
            Subheading::make('count')->state(
                fn ($livewire): string => $livewire->getModel()::count().' orders',
            ),
        ]);
    }
}

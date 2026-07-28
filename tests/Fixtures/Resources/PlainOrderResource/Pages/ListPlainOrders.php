<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\PlainOrderResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use VitisStudio\FilamentHeaderSchema\Components\Heading;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\PlainOrderResource;

/**
 * A header schema is defined, but the page also declares `getHeader()`. The
 * page's own method wins, so a hand-written Blade header still takes over.
 */
class ListPlainOrders extends ListRecords
{
    use HasHeaderSchema;

    protected static string $resource = PlainOrderResource::class;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Heading::make('title')->state('Schema heading'),
        ]);
    }

    public function getHeader(): ?View
    {
        return view('filament-header-schema-tests::custom-header');
    }
}

<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Convention\Schemas;

use Filament\Schemas\Schema;
use VitisStudio\FilamentHeaderSchema\Components\HeaderSection;
use VitisStudio\FilamentHeaderSchema\Components\Heading;

class OrderHeader
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                HeaderSection::make([
                    Heading::make('reference')
                        ->default(fn ($livewire) => $livewire->getHeading()),
                ]),
            ]);
    }
}

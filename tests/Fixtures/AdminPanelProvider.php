<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\OrderResource;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\PlainOrderResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->resources([
                OrderResource::class,
                PlainOrderResource::class,
            ]);
    }
}

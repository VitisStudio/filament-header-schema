<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\BareOrderResource;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Convention\ConventionOrderResource;
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
                BareOrderResource::class,
                ConventionOrderResource::class,
                OrderResource::class,
                PlainOrderResource::class,
            ]);
    }
}

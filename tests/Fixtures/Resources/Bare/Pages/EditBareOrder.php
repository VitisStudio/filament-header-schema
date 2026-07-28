<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Traits\Conditionable;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\BareOrderResource;

/**
 * Already has traits, so the command has to slot in among them.
 */
class EditBareOrder extends EditRecord
{
    use Conditionable;

    protected static string $resource = BareOrderResource::class;

    /**
     * @return array<DeleteAction>
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

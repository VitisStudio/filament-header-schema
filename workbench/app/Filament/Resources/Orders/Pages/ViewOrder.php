<?php

namespace Workbench\App\Filament\Resources\Orders\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use Workbench\App\Filament\Resources\Orders\OrderResource;

/**
 * The convention: the trait and nothing else. The header comes from
 * `Schemas/OrderHeader.php`, which `make:filament-header-schema` generated and
 * this page never names.
 */
class ViewOrder extends ViewRecord
{
    use HasHeaderSchema;

    protected static string $resource = OrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('customer.name'),
            TextEntry::make('customer.email'),
            TextEntry::make('reference'),
            TextEntry::make('status')->badge(),
            TextEntry::make('total')->money('USD'),
            TextEntry::make('placed_at')->dateTime('j M Y, g:ia'),
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('refund')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('danger')
                ->outlined()
                ->requiresConfirmation()
                ->action(fn () => null),
        ];
    }
}

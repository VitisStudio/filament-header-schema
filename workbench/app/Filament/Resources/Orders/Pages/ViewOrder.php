<?php

namespace Workbench\App\Filament\Resources\Orders\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use VitisStudio\FilamentHeaderSchema\Components\HeaderSection;
use VitisStudio\FilamentHeaderSchema\Components\Heading;
use VitisStudio\FilamentHeaderSchema\Components\Subheading;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;
use Workbench\App\Filament\Resources\Orders\OrderResource;
use Workbench\App\Models\Order;

/**
 * The full treatment: an avatar on the leading edge, a heading with a badge
 * row and a description in the middle, and the order total pushed right.
 */
class ViewOrder extends ViewRecord
{
    use HasHeaderSchema;

    protected static string $resource = OrderResource::class;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            HeaderSection::make([
                Heading::make('reference'),
                Flex::make([
                    TextEntry::make('status')->badge()->hiddenLabel()->grow(false),
                    TextEntry::make('placed_at')
                        ->hiddenLabel()
                        ->grow(false)
                        ->date('j M Y')
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->color('gray'),
                    TextEntry::make('items_count')
                        ->hiddenLabel()
                        ->grow(false)
                        ->formatStateUsing(fn (int $state): string => $state.' items')
                        ->icon(Heroicon::OutlinedShoppingBag)
                        ->color('gray'),
                ])->dense(),
                Subheading::make('customer.company'),
            ])
                ->leading(
                    ImageEntry::make('customer.avatar')
                        ->hiddenLabel()
                        ->circular()
                        ->imageSize(64),
                )
                ->trailing([
                    Heading::make('total')
                        ->level(3)
                        ->state(fn (Order $record): string => '$'.number_format((float) $record->total, 2)),
                    Subheading::make('total_label')->state('Order total')->size('sm'),
                ]),
        ]);
    }

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

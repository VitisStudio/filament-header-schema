<?php

namespace Workbench\App\Filament\Resources\Customers\Pages;

use Filament\Actions\Action;
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
use Workbench\App\Filament\Resources\Customers\CustomerResource;
use Workbench\App\Models\Customer;

/**
 * A profile-style header: a large avatar, an icon-decorated heading, and two
 * figures side by side on the trailing edge.
 */
class ViewCustomer extends ViewRecord
{
    use HasHeaderSchema;

    protected static string $resource = CustomerResource::class;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            HeaderSection::make([
                Heading::make('name')
                    ->icon(fn (Customer $record): ?Heroicon => $record->is_priority ? Heroicon::OutlinedStar : null)
                    ->iconPosition('after')
                    ->iconSize('lg'),
                Subheading::make('email')->icon(Heroicon::OutlinedEnvelope)->size('md'),
            ])
                ->leading(
                    ImageEntry::make('avatar')
                        ->hiddenLabel()
                        ->circular()
                        ->imageSize(72),
                )
                ->trailing(
                    Flex::make([
                        TextEntry::make('orders_count')
                            ->label('Orders')
                            ->state(fn (Customer $record): int => $record->orders()->count())
                            ->size('lg')
                            ->weight('bold'),
                        TextEntry::make('lifetime_value')
                            ->label('Lifetime value')
                            ->money('USD')
                            ->size('lg')
                            ->weight('bold'),
                    ]),
                ),
        ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('company'),
            TextEntry::make('joined_at')->date('j M Y'),
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->outlined()
                ->action(fn () => null),
        ];
    }
}

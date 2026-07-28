<?php

namespace Workbench\App\Filament\Resources\Orders;

use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Workbench\App\Enums\OrderStatus;
use Workbench\App\Models\Order;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 1;

    // `make:filament-header-schema` seeds the generated heading with this.
    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('reference')->required(),
            Select::make('customer_id')->relationship('customer', 'name')->required(),
            Select::make('status')->options(OrderStatus::class)->required(),
            TextInput::make('total')->numeric()->prefix('$')->required(),
            TextInput::make('items_count')->numeric()->required(),
            DateTimePicker::make('placed_at')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('customer.name')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('total')->money('USD')->sortable(),
                TextColumn::make('placed_at')->dateTime('j M Y')->sortable(),
            ])
            ->defaultSort('placed_at', 'desc');
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

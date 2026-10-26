<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderRequestResource\Pages;
use App\Models\OrderRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class OrderRequestResource extends Resource
{
    protected static ?string $model = OrderRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) site()->isModuleEnabled('catalog');
    }

    public static function canViewAny(): bool
    {
        return (bool) site()->isModuleEnabled('catalog');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! site()->isModuleEnabled('catalog')) {
            return null;
        }

        $count = OrderRequest::where('status', OrderRequest::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Group::make([
                            Section::make('Order Summary & Items')
                                ->schema([
                                    Repeater::make('items')
                                        ->label('Ordered Items')
                                        ->schema([
                                            TextInput::make('name')->label('Item Name')->disabled(),
                                            TextInput::make('price')->label('Unit Price')->disabled(),
                                            TextInput::make('qty')->label('Quantity')->disabled(),
                                            TextInput::make('subtotal')->label('Subtotal')->disabled(),
                                        ])
                                        ->columns(4)
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false),

                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('total')
                                                ->label('Total Amount')
                                                ->disabled(),
                                            TextInput::make('currency')
                                                ->label('Currency')
                                                ->disabled(),
                                        ]),
                                ]),

                            Section::make('Customer Information')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('customer_name')->label('Name')->disabled(),
                                            TextInput::make('phone')->label('Phone')->tel()->disabled(),
                                        ]),
                                    TextInput::make('email')->label('Email Address')->email()->disabled(),
                                    Textarea::make('address')->label('Delivery Address')->rows(2)->disabled(),
                                    Textarea::make('notes')->label('Customer Notes')->rows(2)->disabled(),
                                ]),
                        ])->columnSpan(['lg' => 2]),

                        Group::make([
                            Section::make('Workflow Status')
                                ->schema([
                                    Select::make('status')
                                        ->label('Order Status')
                                        ->options(OrderRequest::statuses())
                                        ->required(),

                                    TextInput::make('reference')
                                        ->label('Reference Code')
                                        ->disabled(),

                                    DateTimePicker::make('created_at')
                                        ->label('Submitted At')
                                        ->disabled(),
                                ]),
                        ])->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),

                TextColumn::make('total')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn (OrderRequest $record): string => $record->formattedTotal()),

                TextColumn::make('items')
                    ->label('Items')
                    ->formatStateUsing(fn ($state): int => is_array($state) ? count($state) : 0)
                    ->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => OrderRequest::STATUS_NEW,
                        'info' => OrderRequest::STATUS_CONFIRMED,
                        'success' => OrderRequest::STATUS_DELIVERED,
                        'gray' => OrderRequest::STATUS_CANCELLED,
                    ])
                    ->formatStateUsing(fn (string $state): string => OrderRequest::statuses()[$state] ?? $state),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderRequest::statuses()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn (OrderRequest $record): bool => $record->status === OrderRequest::STATUS_NEW)
                    ->action(function (OrderRequest $record) {
                        $record->confirm();
                        Notification::make()->title('Order confirmed.')->success()->send();
                    }),

                Tables\Actions\Action::make('deliver')
                    ->label('Deliver')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn (OrderRequest $record): bool => in_array($record->status, [OrderRequest::STATUS_NEW, OrderRequest::STATUS_CONFIRMED]))
                    ->action(function (OrderRequest $record) {
                        $record->deliver();
                        Notification::make()->title('Order marked as delivered.')->success()->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('confirm_selected')
                        ->label('Confirm Selected')
                        ->icon('heroicon-o-check')
                        ->action(function (Collection $records) {
                            $records->each->confirm();
                            Notification::make()->title('Selected orders confirmed.')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deliver_selected')
                        ->label('Deliver Selected')
                        ->icon('heroicon-o-truck')
                        ->action(function (Collection $records) {
                            $records->each->deliver();
                            Notification::make()->title('Selected orders marked as delivered.')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderRequests::route('/'),
            'view' => Pages\ViewOrderRequest::route('/{record}'),
            'edit' => Pages\EditOrderRequest::route('/{record}/edit'),
        ];
    }
}

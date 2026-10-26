<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
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

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Inquiries';

    protected static ?string $modelLabel = 'Inquiry';

    protected static ?string $pluralModelLabel = 'Inquiries';

    protected static ?int $navigationSort = 15;

    public static function getNavigationBadge(): ?string
    {
        $count = Inquiry::where('status', Inquiry::STATUS_NEW)->count();

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
                            Section::make('Inquiry Content')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Sender Name')
                                        ->disabled(),
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('email')
                                                ->label('Email Address')
                                                ->email()
                                                ->disabled(),
                                            TextInput::make('phone')
                                                ->label('Phone Number')
                                                ->tel()
                                                ->disabled(),
                                        ]),
                                    TextInput::make('subject')
                                        ->label('Subject')
                                        ->disabled(),
                                    Textarea::make('message')
                                        ->label('Message')
                                        ->rows(8)
                                        ->disabled(),
                                ]),
                        ])->columnSpan(['lg' => 2]),

                        Group::make([
                            Section::make('Metadata & Status')
                                ->schema([
                                    Select::make('status')
                                        ->label('Inquiry Status')
                                        ->options(Inquiry::statuses())
                                        ->required(),
                                    DateTimePicker::make('handled_at')
                                        ->label('Handled At')
                                        ->disabled(),
                                    TextInput::make('ip')
                                        ->label('Sender IP Address')
                                        ->disabled(),
                                    TextInput::make('source_url')
                                        ->label('Origin URL')
                                        ->disabled(),
                                    DateTimePicker::make('created_at')
                                        ->label('Received At')
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
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone')
                    ->placeholder('—'),

                TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(28)
                    ->placeholder('General Inquiry'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => Inquiry::STATUS_NEW,
                        'info' => Inquiry::STATUS_READ,
                        'success' => Inquiry::STATUS_REPLIED,
                        'gray' => Inquiry::STATUS_ARCHIVED,
                    ]),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(Inquiry::statuses()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('mark_as_read')
                    ->label('Mark Read')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn (Inquiry $record): bool => $record->status === Inquiry::STATUS_NEW)
                    ->action(function (Inquiry $record) {
                        $record->markAsRead();
                        Notification::make()
                            ->title('Inquiry marked as read.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_as_replied')
                    ->label('Mark Replied')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (Inquiry $record): bool => in_array($record->status, [Inquiry::STATUS_NEW, Inquiry::STATUS_READ]))
                    ->action(function (Inquiry $record) {
                        $record->markAsReplied();
                        Notification::make()
                            ->title('Inquiry marked as replied.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_as_read')
                        ->label('Mark as Read')
                        ->icon('heroicon-o-check')
                        ->action(function (Collection $records) {
                            $records->each->markAsRead();
                            Notification::make()
                                ->title('Selected inquiries marked as read.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('mark_as_archived')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->action(function (Collection $records) {
                            $records->each->markAsArchived();
                            Notification::make()
                                ->title('Selected inquiries archived.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            'view' => Pages\ViewInquiry::route('/{record}'),
        ];
    }
}

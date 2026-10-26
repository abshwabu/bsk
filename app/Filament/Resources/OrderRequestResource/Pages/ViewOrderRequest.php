<?php

namespace App\Filament\Resources\OrderRequestResource\Pages;

use App\Filament\Resources\OrderRequestResource;
use App\Models\OrderRequest;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderRequest extends ViewRecord
{
    protected static string $resource = OrderRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Print Order')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes([
                    'onclick' => 'window.print()',
                ]),

            Action::make('confirm')
                ->label('Confirm Order')
                ->icon('heroicon-o-check')
                ->color('info')
                ->visible(fn (): bool => $this->record->status === OrderRequest::STATUS_NEW)
                ->action(function () {
                    $this->record->confirm();
                    Notification::make()->title('Order confirmed.')->success()->send();
                }),

            Action::make('deliver')
                ->label('Mark Delivered')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->visible(fn (): bool => in_array($this->record->status, [OrderRequest::STATUS_NEW, OrderRequest::STATUS_CONFIRMED]))
                ->action(function () {
                    $this->record->deliver();
                    Notification::make()->title('Order marked as delivered.')->success()->send();
                }),

            Action::make('cancel')
                ->label('Cancel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status !== OrderRequest::STATUS_CANCELLED)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->cancel();
                    Notification::make()->title('Order cancelled.')->warning()->send();
                }),

            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

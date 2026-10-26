<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInquiry extends ViewRecord
{
    protected static string $resource = InquiryResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record instanceof Inquiry && $this->record->status === Inquiry::STATUS_NEW) {
            $this->record->markAsRead();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_as_replied')
                ->label('Mark Replied')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success')
                ->visible(fn (): bool => in_array($this->record->status, [Inquiry::STATUS_NEW, Inquiry::STATUS_READ]))
                ->action(function () {
                    $this->record->markAsReplied();
                    Notification::make()
                        ->title('Inquiry marked as replied.')
                        ->success()
                        ->send();
                }),

            Action::make('mark_as_archived')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->visible(fn (): bool => $this->record->status !== Inquiry::STATUS_ARCHIVED)
                ->action(function () {
                    $this->record->markAsArchived();
                    Notification::make()
                        ->title('Inquiry archived.')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}

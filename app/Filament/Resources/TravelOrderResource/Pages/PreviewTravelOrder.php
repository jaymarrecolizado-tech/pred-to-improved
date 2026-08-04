<?php

namespace App\Filament\Resources\TravelOrderResource\Pages;

use App\Filament\Resources\TravelOrderResource;
use App\Services\TravelOrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class PreviewTravelOrder extends ViewRecord
{
    protected static string $resource = TravelOrderResource::class;

    protected static string $view = 'filament.resources.travel-order-resource.pages.preview-travel-order';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing('user');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Review Travel Order';
    }

    public function getHeading(): string|Htmlable
    {
        $code = $this->record->to_code ?: 'Draft';

        return 'Review Travel Order — ' . $code;
    }

    protected function authorizeAccess(): void
    {
        abort_unless(
            in_array($this->record->status, ['DRAFT', 'FOR_REVISION'], true)
            && (
                $this->record->user_id === auth()->id()
                || (auth()->user()?->isAdmin() ?? false)
            ),
            403
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->label('Submit for approval')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () =>
                    $this->record->status === 'DRAFT'
                    && $this->record->user_id === auth()->id()
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Travel Order')
                ->modalDescription('This will send the travel order to the first approver and notify listed travelers who have user accounts.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'SUBMITTED',
                        'submitted_at' => now(),
                    ]);

                    app(TravelOrderService::class)->createApprovals($this->record);

                    Notification::make()
                        ->title('Order Submitted')
                        ->success()
                        ->send();

                    $this->redirect(TravelOrderResource::getUrl('view', ['record' => $this->record]));
                }),

            Actions\Action::make('resubmit')
                ->label('Resubmit for approval')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn () =>
                    $this->record->status === 'FOR_REVISION'
                    && $this->record->user_id === auth()->id()
                )
                ->requiresConfirmation()
                ->modalHeading('Resubmit Travel Order')
                ->modalDescription('Your travel order will be sent back to the approver who requested the revision.')
                ->action(function () {
                    app(TravelOrderService::class)->resubmitAfterRevision($this->record);

                    Notification::make()
                        ->title('Travel Order Resubmitted')
                        ->success()
                        ->body('Your travel order has been sent back for approval.')
                        ->send();

                    $this->redirect(TravelOrderResource::getUrl('view', ['record' => $this->record]));
                }),

            Actions\Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil')
                ->color('gray')
                ->visible(fn () =>
                    in_array($this->record->status, ['DRAFT', 'FOR_REVISION'], true)
                    && $this->record->user_id === auth()->id()
                )
                ->url(fn () => TravelOrderResource::getUrl('edit', ['record' => $this->record])),

            Actions\Action::make('back')
                ->label('Back to list')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => TravelOrderResource::getUrl('index')),
        ];
    }
}

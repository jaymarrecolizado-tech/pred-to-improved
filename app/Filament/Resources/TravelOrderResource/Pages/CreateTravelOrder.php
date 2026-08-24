<?php

namespace App\Filament\Resources\TravelOrderResource\Pages;

use App\Filament\Resources\TravelOrderResource;
use App\Models\TravelOrder;
use App\Models\Employee;
use App\Services\TravelOrderService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CreateTravelOrder extends CreateRecord
{
    protected static string $resource = TravelOrderResource::class;

    public bool $submitAfterSave = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('saveAsDraft')
                ->label('Save as Draft')
                ->icon('heroicon-o-document')
                ->color('gray')
                ->action(function () {
                    $this->submitAfterSave = false;
                    $this->create();
                }),

            Action::make('submitForApproval')
                ->label('Submit for Approval')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Submit Travel Order')
                ->modalDescription('Your travel order will be submitted for approval immediately. Make sure all details are correct.')
                ->action(function () {
                    $this->submitAfterSave = true;
                    $this->create();
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->url($this->getResource()::getUrl('index'))
                ->outlined(),
        ];
    }

    protected function afterCreate(): void
    {
        if ($this->submitAfterSave) {
            $this->record->update([
                'status'       => 'SUBMITTED',
                'submitted_at' => now(),
            ]);

            app(TravelOrderService::class)->createApprovals($this->record);

            Notification::make()
                ->title('Travel Order Submitted')
                ->success()
                ->body('Your travel order has been submitted for approval.')
                ->send();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status']  = 'DRAFT';

        $travelers = $data['travelers'] ?? [];

        foreach ($travelers as $index => $traveler) {
            if (!empty($traveler['employee_id'])) {
                $employee = Employee::with('division')->find($traveler['employee_id']);
                if ($employee) {
                    $travelers[$index]['employee_id'] = (int) $traveler['employee_id'];
                    $travelers[$index]['name']        = $employee->full_name;
                    $travelers[$index]['position']    = $employee->position;
                    $travelers[$index]['division']    = $employee->division?->name;
                }
            }
            unset($travelers[$index]['info']);
        }

        $data['travelers'] = $travelers;

        return $data;
    }

    protected function beforeCreate(): void
    {

        if (auth()->user()->isSuperAdmin()) return;

        $data      = $this->form->getState();
        $travelers = $data['travelers'] ?? [];

        foreach ($travelers as $traveler) {
            $employeeId = $traveler['employee_id'] ?? null;
            if (!$employeeId) continue;

            $hasConflict = TravelOrder::hasConflictingTravelOrder(
                $employeeId,
                $data['start_date'],
                $data['end_date'],
            );

            if ($hasConflict) {
                $employee = Employee::find($employeeId);
                Notification::make()
                    ->title('Scheduling Conflict')
                    ->danger()
                    ->body(
                        ($employee?->full_name ?? 'A traveler') .
                        ' already has an approved or pending travel order during this period.'
                    )
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }
    }
}

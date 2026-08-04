<?php

namespace App\Filament\Resources\TravelOrderResource\Pages;

use App\Filament\Resources\TravelOrderResource;
use App\Models\TravelOrder;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateTravelOrder extends CreateRecord
{
    protected static string $resource = TravelOrderResource::class;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Save & review');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['status'] = 'DRAFT';

        // Check each selected traveler for overlapping travel orders on the same dates
        $this->validateTravelersForConflicts($data);

        // Keep employee_id for conflict detection; drop UI-only helper fields
        if (isset($data['travelers'])) {
            foreach ($data['travelers'] as &$traveler) {
                unset($traveler['info']);
            }
            unset($traveler);
        }

        return $data;
    }

    /**
     * Validate that no traveler has a conflicting travel order
     * Throws an exception if conflicts are found
     */
    protected function validateTravelersForConflicts(array $data): void
    {
        if (!isset($data['travelers']) || empty($data['travelers'])) {
            return;
        }

        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $conflicts = [];

        foreach ($data['travelers'] as $traveler) {
            if (!isset($traveler['employee_id']) || !$traveler['employee_id']) {
                continue;
            }

            $employeeId = $traveler['employee_id'];

            // Query existing non-cancelled orders that overlap with the requested travel period
            if (TravelOrder::hasConflictingTravelOrder($employeeId, $startDate, $endDate)) {
                $conflictingOrders = TravelOrder::getConflictingTravelOrders($employeeId, $startDate, $endDate);

                $employeeName = $traveler['name'] ?? 'Employee ID: ' . $employeeId;
                $conflictDates = $conflictingOrders->map(function ($order) {
                    return $order->start_date->format('M d, Y') . ' - ' . $order->end_date->format('M d, Y');
                })->join(', ');

                $conflicts[] = "{$employeeName} already has a travel order for: {$conflictDates}";
            }
        }

        if (!empty($conflicts)) {
            Notification::make()
                ->title('Conflicting Travel Orders Found')
                ->body('The following personnel cannot be added: ' . implode('; ', $conflicts))
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'travelers' => 'One or more travelers already have travel orders for the specified dates.'
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('preview', ['record' => $this->record]);
    }
}

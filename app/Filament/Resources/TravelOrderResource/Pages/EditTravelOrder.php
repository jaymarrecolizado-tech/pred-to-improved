<?php

namespace App\Filament\Resources\TravelOrderResource\Pages;

use App\Filament\Resources\TravelOrderResource;
use App\Models\Employee;
use App\Models\TravelOrder;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditTravelOrder extends EditRecord
{
    protected static string $resource = TravelOrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Conflict check is skipped in revision mode - travel dates may be in the past
        // and the requestor is correcting existing data, not creating a new booking
        if ($this->record->status !== 'FOR_REVISION') {
            $this->checkTravelerConflicts($data, $this->record->id);
        }

        if (isset($data['travelers'])) {
            foreach ($data['travelers'] as &$traveler) {
                unset($traveler['info']);
            }
            unset($traveler);
        }

        return $data;
    }

    protected function checkTravelerConflicts(array $data, ?int $excludeId = null): void
    {
        $startDate = $data['start_date'] ?? null;
        $endDate   = $data['end_date'] ?? null;
        $travelers = $data['travelers'] ?? [];

        if (!$startDate || !$endDate || empty($travelers)) return;

        $errors = [];

        foreach ($travelers as $traveler) {
            $employeeId = $traveler['employee_id'] ?? null;
            if (!$employeeId) continue;

            $conflict = TravelOrder::hasConflictingTravelOrder(
                $employeeId,
                $startDate,
                $endDate,
                $excludeId
            );

            if ($conflict) {
                $employee = Employee::find($employeeId);
                $name     = $employee?->full_name ?? 'Employee #' . $employeeId;
                $errors[] = "{$name} already has a conflicting travel order within the selected dates.";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'travelers' => $errors,
            ]);
        }
    }
}
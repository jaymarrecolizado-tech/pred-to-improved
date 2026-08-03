<?php

namespace App\Filament\Resources\TravelApprovalResource\Pages;

use App\Filament\Resources\TravelApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTravelApproval extends EditRecord
{
    protected static string $resource = TravelApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->icon('heroicon-o-trash'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

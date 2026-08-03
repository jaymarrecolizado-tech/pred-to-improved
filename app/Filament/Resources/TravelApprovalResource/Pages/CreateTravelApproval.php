<?php

namespace App\Filament\Resources\TravelApprovalResource\Pages;

use App\Filament\Resources\TravelApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTravelApproval extends CreateRecord
{
    protected static string $resource = TravelApprovalResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

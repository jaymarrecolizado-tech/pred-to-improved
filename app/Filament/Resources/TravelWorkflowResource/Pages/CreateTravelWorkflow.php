<?php

namespace App\Filament\Resources\TravelWorkflowResource\Pages;

use App\Filament\Resources\TravelWorkflowResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTravelWorkflow extends CreateRecord
{
    protected static string $resource = TravelWorkflowResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

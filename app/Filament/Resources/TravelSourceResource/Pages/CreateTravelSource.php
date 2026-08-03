<?php

namespace App\Filament\Resources\TravelSourceResource\Pages;

use App\Filament\Resources\TravelSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTravelSource extends CreateRecord
{
    protected static string $resource = TravelSourceResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

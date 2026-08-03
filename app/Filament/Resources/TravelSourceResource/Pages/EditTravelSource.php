<?php

namespace App\Filament\Resources\TravelSourceResource\Pages;

use App\Filament\Resources\TravelSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTravelSource extends EditRecord
{
    protected static string $resource = TravelSourceResource::class;

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

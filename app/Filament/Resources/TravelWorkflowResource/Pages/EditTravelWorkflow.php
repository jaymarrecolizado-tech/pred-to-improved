<?php

namespace App\Filament\Resources\TravelWorkflowResource\Pages;

use App\Filament\Resources\TravelWorkflowResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTravelWorkflow extends EditRecord
{
    protected static string $resource = TravelWorkflowResource::class;

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

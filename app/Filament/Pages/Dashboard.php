<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TravelOrderResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createTravelOrder')
                ->label('Create Travel Order')
                ->icon('heroicon-o-plus')
                ->url(TravelOrderResource::getUrl('create'))
                ->visible(fn () => TravelOrderResource::canCreate()),
        ];
    }
}

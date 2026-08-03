<?php

namespace App\Filament\Resources\TravelOrderResource\Pages;

use App\Filament\Resources\TravelOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TravelOrderResource\Widgets\StatsWidget;

class ListTravelOrders extends ListRecords
{
    protected static string $resource = TravelOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-o-plus')->label('Create Travel Order'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsWidget::class,
        ];
    }
}

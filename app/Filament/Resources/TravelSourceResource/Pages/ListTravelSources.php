<?php

namespace App\Filament\Resources\TravelSourceResource\Pages;

use App\Filament\Resources\TravelSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TravelSourceResource\Widgets\StatsWidget;

class ListTravelSources extends ListRecords
{
    protected static string $resource = TravelSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsWidget::class,
        ];
    }
}

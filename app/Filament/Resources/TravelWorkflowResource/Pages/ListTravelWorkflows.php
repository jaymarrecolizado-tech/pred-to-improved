<?php

namespace App\Filament\Resources\TravelWorkflowResource\Pages;

use App\Filament\Resources\TravelWorkflowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TravelWorkflowResource\Widgets\StatsWidget;

class ListTravelWorkflows extends ListRecords
{
    protected static string $resource = TravelWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-o-plus')->label('Create Workflow'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsWidget::class,
        ];
    }
}

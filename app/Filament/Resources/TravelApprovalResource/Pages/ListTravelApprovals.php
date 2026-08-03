<?php

namespace App\Filament\Resources\TravelApprovalResource\Pages;

use App\Filament\Resources\TravelApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TravelApprovalResource\Widgets\StatsWidget;

class ListTravelApprovals extends ListRecords
{
    protected static string $resource = TravelApprovalResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            StatsWidget::class,
        ];
    }
}

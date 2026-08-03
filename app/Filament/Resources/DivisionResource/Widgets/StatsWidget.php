<?php

namespace App\Filament\Resources\DivisionResource\Widgets;

use App\Models\Division;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWidget extends BaseWidget
{
    protected ?string $heading = 'Division Overview';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Employees', Employee::count())
                ->description('Staff across all divisions')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Divisions', Division::count())
                ->description('Active organization units')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
        ];
    }
}

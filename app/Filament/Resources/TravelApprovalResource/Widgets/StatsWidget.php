<?php

namespace App\Filament\Resources\TravelApprovalResource\Widgets;

use App\Models\TravelApproval;
use App\Models\TravelOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWidget extends BaseWidget
{
    protected ?string $heading = 'Travel Approval Overview';

    protected function getStats(): array
    {
        $pending = TravelApproval::where('status', 'PENDING')->count();
        $completed = TravelOrder::where('status', 'COMPLETED')->count();
        $rejected = TravelApproval::where('status', 'REJECTED')->count();

        return [
            Stat::make('Pending', $pending)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Completed', $completed)
                ->description('Completed travel orders')
                ->descriptionIcon('heroicon-m-flag')
                ->color('success'),

            Stat::make('Rejected', $rejected)
                ->description('Rejected travel orders')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}

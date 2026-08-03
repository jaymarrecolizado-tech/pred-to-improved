<?php

namespace App\Filament\Resources\TravelOrderResource\Widgets;

use App\Models\TravelOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected ?string $heading = 'Travel Orders Overview';

    protected function getStats(): array
    {
        $userId = Auth::id();

        $counts = TravelOrder::where('user_id', $userId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusConfigs = [
            'DRAFT' => [
                'label' => 'Draft',
                'icon'  => 'heroicon-m-clipboard',
                'color' => 'gray',
                'desc'  => 'To Submit',
            ],
            'PENDING' => [
                'label' => 'Pending',
                'icon'  => 'heroicon-m-clock',
                'color' => 'warning',
                'desc'  => 'Awaiting Approval',
            ],
            'COMPLETED' => [
                'label' => 'Completed',
                'icon'  => 'heroicon-m-check-badge',
                'color' => 'success',
                'desc'  => 'Finalized Travel Orders',
            ],
            'REJECTED' => [
                'label' => 'Rejected',
                'icon'  => 'heroicon-m-x-circle',
                'color' => 'danger',
                'desc'  => 'Declined Requests',
            ],
        ];

        return collect($statusConfigs)->map(function ($config, $status) use ($counts, $userId) {
            $count = $counts->get($status, 0);

            return Stat::make($config['label'], number_format($count))
                ->description($config['desc'])
                ->descriptionIcon($config['icon'])
                ->color($config['color'])
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:scale-[1.02] transition-transform',
                ]);
        })->values()->toArray();
    }

    /**
     * Generates a trend line for the last 6 months for a given status.
     */
    private function getStatusTrend(string $status, int $userId): array
    {
        $monthsToTrack = 6;
        $trend         = [];

        for ($i = $monthsToTrack; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $count = TravelOrder::where('user_id', $userId)
                ->where('status', $status)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $trend[] = $count;
        }

        return $trend;
    }
}
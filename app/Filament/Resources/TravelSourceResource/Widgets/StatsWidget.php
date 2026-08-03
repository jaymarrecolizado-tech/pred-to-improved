<?php

namespace App\Filament\Resources\TravelSourceResource\Widgets;

use App\Models\TravelSource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWidget extends BaseWidget
{
    protected ?string $heading = 'Logistics Overview';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Fetch travel source usage counts from approved and completed travel orders
        $sources = TravelSource::get();
        $totalVehicles = $sources->sum(fn ($s) => count($s->vehicles ?? []));
        $topSource = $sources->sortByDesc(fn ($s) => count($s->vehicles ?? []))->first();

        // Define display configuration for each travel source stat card
        $stats = [
            'sources' => [
                'label' => 'Fund Sources',
                'value' => $sources->count(),
                'desc'  => 'Available funding types',
                'icon'  => 'heroicon-m-currency-dollar',
                'color' => 'success',
            ],
            'fleet' => [
                'label' => 'Total Fleet',
                'value' => $totalVehicles,
                'desc'  => 'Total registered vehicles',
                'icon'  => 'heroicon-m-truck',
                'color' => 'info',
            ],
        ];

        // Build Filament Stat components from the configuration and fetched counts
        return collect($stats)->map(fn ($stat) =>
            Stat::make($stat['label'], $stat['value'])
                ->description($stat['desc'])
                ->descriptionIcon($stat['icon'])
                ->color($stat['color'])
                ->extraAttributes(['class' => 'cursor-pointer'])
        )->values()->toArray();
    }
}

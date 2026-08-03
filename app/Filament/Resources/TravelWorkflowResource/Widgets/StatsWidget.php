<?php

namespace App\Filament\Resources\TravelWorkflowResource\Widgets;

use App\Models\TravelWorkflow;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = Auth::id();

        // Workflow stat definitions - each entry maps a workflow flag to its display label and icon
        $configs = [
            'approvers' => [
                'label' => 'Approvers',
                'desc'  => 'Number of approvers assigned',
                'icon'  => 'heroicon-o-check-circle',
                'color' => 'primary',
                'value' => TravelWorkflow::where('user_id', $userId)->distinct('approver_id')->count('approver_id'),
            ],
            'to_code_provider' => [
                'label' => 'TO Number Providers',
                'desc'  => 'Users allowed to assign TO codes',
                'icon'  => 'heroicon-o-qr-code',
                'color' => 'info',
                'value' => TravelWorkflow::where('user_id', $userId)->where('to_code_provider', true)->count(),
            ],
            'active' => [
                'label' => 'Active Workflows',
                'desc'  => 'Currently active approval workflows',
                'icon'  => 'heroicon-o-bolt',
                'color' => 'success',
                'value' => TravelWorkflow::where('user_id', $userId)->where('active', true)->count(),
            ],
            'notify_email' => [
                'label' => 'Notify Email Enabled',
                'desc'  => 'Workflows with email notifications on',
                'icon'  => 'heroicon-o-envelope',
                'color' => 'warning',
                'value' => TravelWorkflow::where('user_id', $userId)->where('notify_email', true)->count(),
            ],
        ];

        // Build Filament Stat components from workflow configuration and live counts
        return collect($configs)->map(function ($config, $key) use ($userId) {
            return Stat::make($config['label'], $config['value'])
                ->description($config['desc'])
                ->descriptionIcon(str_replace('-o-', '-m-', $config['icon']))                ->color($config['color'])
                ->extraAttributes(['class' => 'cursor-pointer']);
        })->values()->toArray();
    }

    private function getMonthlyTrend(int $userId, string $field): array
    {
        $query = TravelWorkflow::query()
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->where('user_id', $userId)
            ->whereYear('created_at', now()->year);

        // Initialize a 12-month trend array and fill with actual monthly counts
        if (in_array($field, ['active', 'notify_email', 'to_code_provider'])) {
            $query->where($field, true);
        }

        $data = $query->groupBy('month')->pluck('count', 'month')->toArray();

        $fullYear = array_replace(array_fill(1, 12, 0), $data);

        // Return only the last 7 months — enough data points for a readable sparkline without overcrowding
        return array_slice(array_values($fullYear), -7);
    }
}

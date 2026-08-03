<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Single query to get user counts per role — avoids multiple separate queries
        $counts = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->mapWithKeys(fn ($total, $role) => [strtoupper($role) => $total]);

        $totalUsers = $counts->sum();
        $totalAdmins = $counts->get('ADMIN', 0);
        $totalEmployees = $counts->get('EMPLOYEE', 0);

        // Build monthly registration trend for the last 6 months for the sparkline chart
        $registrationTrend = $this->getRegistrationTrend();

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->description('Platform growth')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Employees', number_format($totalEmployees))
                ->description('Active workforce')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),

            Stat::make('Admins', number_format($totalAdmins))
                ->description('System moderators')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),
        ];
    }

    /**
     * Calculates user registration trend for the last 7 days
     */
    private function getRegistrationTrend(): array
    {
        return collect(range(6, 0))->map(function ($days) {
            return User::whereDate('created_at', now()->subDays($days))->count();
        })->toArray();
    }

    /**
     * Calculates trend per role for the last 7 days
     */
    private function getRoleTrend(string $role): array
    {
        return collect(range(6, 0))->map(function ($days) use ($role) {
            return User::where('role', $role)
                ->whereDate('created_at', now()->subDays($days))
                ->count();
        })->toArray();
    }
}

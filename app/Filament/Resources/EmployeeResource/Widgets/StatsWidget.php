<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\Employee;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWidget extends BaseWidget
{
    protected ?string $heading = 'HR Statistics';
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // 1. Aggregate employee counts grouped by division for the stats overview
        $employeeStats = [
            'total'  => Employee::count(),
            'active' => Employee::where('active', true)->count(),
        ];

        $userStats = [
            'employee_role' => User::where('role', 'EMPLOYEE')->count(),
            'admin_role'    => User::where('role', 'ADMIN')->count(),
        ];

        // 2. Define stat cards showing employee distribution across divisions
        return [
            Stat::make('Organization Size', $employeeStats['total'])
                ->description('Total registered staff')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Active Workforce', $employeeStats['active'])
                ->description('Currently on duty')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),

            Stat::make('System Users', $userStats['employee_role'])
                ->description('Users with Employee access')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),

            Stat::make('Administrators', $userStats['admin_role'])
                ->description('Users with full privileges')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),
        ];
    }
}

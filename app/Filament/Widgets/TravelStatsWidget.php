<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\TravelOrder;
use App\Models\TravelWorkflow;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TravelStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        return $isAdmin ? $this->getAdminStats($user) : $this->getEmployeeStats($user);
    }

    private function getAdminStats($user): array
    {
        $completed = TravelOrder::where('status', 'COMPLETED')->count();
        $rejected = TravelOrder::where('status', 'REJECTED')->count();

        return [
            Stat::make('Total Employees', Employee::count())
                ->description('All registered employees')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Travel Orders Status', $completed + $rejected)
                ->description("Completed: {$completed} | Rejected: {$rejected}")
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('success'),

            Stat::make('Travel Orders', TravelOrder::count())
                ->description($this->getPeriodDescription())
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),

            $this->getWorkflowStat($user->id, 'Admin Workflow Overview'),
        ];
    }

    private function getEmployeeStats($user): array
    {
        $completed = TravelOrder::where('user_id', $user->id)->where('status', 'COMPLETED')->count();
        $rejected = TravelOrder::where('user_id', $user->id)->where('status', 'REJECTED')->count();

        return [
            $this->getWorkflowStat($user->id, 'My Travel Workflows'),

            Stat::make('My Travel Order Status', $completed + $rejected)
                ->description("Completed: {$completed} | Rejected: {$rejected}")
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('My Total Travel Orders', TravelOrder::where('user_id', $user->id)->count())
                ->description($this->getPeriodDescription($user->id))
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),
        ];
    }

    /**
     * Shared logic for Workflow Stats
     */
    private function getWorkflowStat(int $userId, string $label): Stat
    {
        $active = TravelWorkflow::where('user_id', $userId)->where('active', true)->count();
        $toApprove = TravelWorkflow::where('user_id', $userId)->where('to_approve', true)->count();

        return Stat::make($label, TravelWorkflow::where('user_id', $userId)->count())
            ->description("To Approve: {$toApprove} | Active: {$active}")
            ->descriptionIcon('heroicon-o-briefcase')
            ->color('warning');
    }

    /**
     * Helper to generate "Monthly: X | Yearly: Y" text
     */
    private function getPeriodDescription(?int $userId = null): string
    {
        $query = TravelOrder::query()->when($userId, fn($q) => $q->where('user_id', $userId));

        $monthly = (clone $query)->whereMonth('created_at', now()->month)->count();
        $yearly = (clone $query)->whereYear('created_at', now()->year)->count();

        return "Monthly: {$monthly} | Yearly: {$yearly}";
    }
}

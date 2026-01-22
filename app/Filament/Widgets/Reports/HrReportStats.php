<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\TenantContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HrReportStats extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return [
                Stat::make(__('reports.stats.employees'), 0),
                Stat::make(__('reports.stats.attendances_today'), 0),
                Stat::make(__('reports.stats.pending_leaves'), 0),
            ];
        }

        $employees = (int) Employee::query()
            ->where('tenant_id', $tenantId)
            ->count();

        $attendancesToday = (int) Attendance::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('date', now()->toDateString())
            ->count();

        $pendingLeaves = (int) LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make(__('reports.stats.employees'), $employees),
            Stat::make(__('reports.stats.attendances_today'), $attendancesToday),
            Stat::make(__('reports.stats.pending_leaves'), $pendingLeaves),
        ];
    }
}

<?php

namespace App\Filament\Widgets\Hr;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\TenantContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class HrStats extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected function getHeading(): ?string
    {
        return 'ملخص الموارد البشرية';
    }

    protected function getStats(): array
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return [
                Stat::make('عدد الموظفين', 0),
                Stat::make('حضور اليوم', 0),
                Stat::make('إجازات معلّقة', 0),
            ];
        }

        $employees = Employee::query()
            ->where('tenant_id', $tenantId)
            ->count();

        $todayAttendances = Attendance::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->count();

        $pendingLeaves = LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('عدد الموظفين', $employees),
            Stat::make('حضور اليوم', $todayAttendances),
            Stat::make('إجازات معلّقة', $pendingLeaves),
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return Gate::forUser($user)->check('widget_HrStats');
    }
}

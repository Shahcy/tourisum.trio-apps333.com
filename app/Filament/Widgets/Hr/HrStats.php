<?php

namespace App\Filament\Widgets\Hr;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class HrStats extends StatsOverviewWidget
{
    protected function getHeading(): ?string
    {
        return 'ملخص الموارد البشرية';
    }

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()?->getKey();

        $employeesCount = Employee::query()
            ->where('tenant_id', $tenantId)
            ->count();

        $today = now()->toDateString();

        $todayAttendances = Attendance::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('date', $today)
            ->count();

        $pendingLeaves = LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('عدد الموظفين', $employeesCount),
            Stat::make('حضور اليوم', $todayAttendances),
            Stat::make('إجازات معلّقة', $pendingLeaves),
        ];
    }
    public static function canView(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) (
            Gate::forUser($user)->check('widget_HrStats')
            || $user?->hasAnyRole(['admin', 'super_admin'])
        );
    }
}

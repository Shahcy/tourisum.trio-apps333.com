<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Attendance;
use App\Support\TenantContext;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentAttendancesTable extends TableWidget
{
    protected static ?string $heading = 'reports.widgets.recent_attendance';
    protected static bool $isLazy = true;

    protected function getTableQuery(): Builder
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return Attendance::query()->whereRaw('1 = 0');
        }

        return Attendance::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('date')
            ->limit(20);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('date')
                ->label(__('hr.attendance.fields.date'))
                ->date(),

            Tables\Columns\TextColumn::make('employee.full_name')
                ->label(__('hr.attendance.fields.employee'))
                ->searchable(),

            Tables\Columns\TextColumn::make('check_in')
                ->label(__('hr.attendance.fields.check_in_short'))
                ->toggleable(),

            Tables\Columns\TextColumn::make('check_out')
                ->label(__('hr.attendance.fields.check_out_short'))
                ->toggleable(),
        ];
    }

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return static::$heading ? __(static::$heading) : null;
    }
}

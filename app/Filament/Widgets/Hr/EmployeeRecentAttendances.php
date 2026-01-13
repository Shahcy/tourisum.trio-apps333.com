<?php

namespace App\Filament\Widgets\Hr;

use App\Models\Attendance;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRecentAttendances extends BaseWidget
{
    // لازم يكون مهيّأ
    public ?int $employeeId = null;

    protected static bool $isLazy = false;

    public function mount(?int $employeeId = null): void
    {
        $this->employeeId = $employeeId;
    }

    protected function getHeading(): ?string
    {
        return 'آخر الحضور';
    }

    protected function getTableQuery(): Builder
    {
        // لو ما وصل employeeId، ما نرجّع سجلات (بدل ما ينهار)
        if (! $this->employeeId) {
            return Attendance::query()->whereRaw('1 = 0');
        }

        $tenantId = Filament::getTenant()?->getKey();

        return Attendance::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $this->employeeId)
            ->latest('date');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('date')->label('التاريخ')->date()->sortable(),
            Tables\Columns\TextColumn::make('check_in')->label('دخول'),
            Tables\Columns\TextColumn::make('check_out')->label('خروج'),
        ];
    }

    protected function getDefaultTableRecordsPerPage(): int
    {
        return 10;
    }
}

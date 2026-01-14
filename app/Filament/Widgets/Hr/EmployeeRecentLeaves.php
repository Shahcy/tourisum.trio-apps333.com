<?php

namespace App\Filament\Widgets\Hr;

use App\Models\LeaveRequest;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRecentLeaves extends BaseWidget
{
    public int $employeeId;

    protected static bool $isLazy = true;

    protected function getHeading(): ?string
    {
        return 'آخر الإجازات';
    }

    protected function getTableQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->getKey();

        return LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $this->employeeId)
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('type')->label('النوع')->badge(),
            Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
            Tables\Columns\TextColumn::make('start_date')->label('من')->date(),
            Tables\Columns\TextColumn::make('end_date')->label('إلى')->date(),
        ];
    }

    protected function getDefaultTableRecordsPerPage(): int
    {
        return 5;
    }
}

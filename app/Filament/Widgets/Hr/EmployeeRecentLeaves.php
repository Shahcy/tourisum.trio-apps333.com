<?php

namespace App\Filament\Widgets\Hr;

use App\Models\LeaveRequest;
use App\Support\TenantContext;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRecentLeaves extends BaseWidget
{
    public ?int $employeeId = null;

    protected static bool $isLazy = true;

    public function mount(?int $employeeId = null): void
    {
        $this->employeeId = $employeeId;
    }

    protected function getHeading(): ?string
    {
        return 'آخر الإجازات';
    }

    protected function getTableQuery(): Builder
    {
        if (! $this->employeeId) {
            return LeaveRequest::query()->whereRaw('1 = 0');
        }

        $tenantId = TenantContext::requireId();

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

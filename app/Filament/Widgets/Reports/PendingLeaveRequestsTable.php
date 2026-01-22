<?php

namespace App\Filament\Widgets\Reports;

use App\Models\LeaveRequest;
use App\Support\TenantContext;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingLeaveRequestsTable extends TableWidget
{
    protected static ?string $heading = 'reports.widgets.pending_leave_requests';
    protected static bool $isLazy = true;

    protected function getTableQuery(): Builder
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return LeaveRequest::query()->whereRaw('1 = 0');
        }

        return LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->orderByDesc('start_date');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('employee.full_name')
                ->label(__('hr.leave_requests.fields.employee'))
                ->searchable(),

            Tables\Columns\TextColumn::make('start_date')
                ->label(__('hr.leave_requests.fields.start_date'))
                ->date(),

            Tables\Columns\TextColumn::make('end_date')
                ->label(__('hr.leave_requests.fields.end_date'))
                ->date(),

            Tables\Columns\TextColumn::make('type')
                ->label(__('hr.leave_requests.fields.type'))
                ->formatStateUsing(fn(?string $state) => match ($state) {
                    'annual' => __('hr.leave_requests.types.annual'),
                    'sick' => __('hr.leave_requests.types.sick'),
                    'unpaid' => __('hr.leave_requests.types.unpaid'),
                    'other' => __('hr.leave_requests.types.other'),
                    default => (string) $state,
                }),

            Tables\Columns\TextColumn::make('status')
                ->label(__('hr.leave_requests.fields.status'))
                ->badge()
                ->formatStateUsing(fn(?string $state) => match ($state) {
                    'pending' => __('hr.leave_requests.statuses.pending'),
                    'approved' => __('hr.leave_requests.statuses.approved'),
                    'rejected' => __('hr.leave_requests.statuses.rejected'),
                    default => (string) $state,
                }),
        ];
    }

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return static::$heading ? __(static::$heading) : null;
    }
}

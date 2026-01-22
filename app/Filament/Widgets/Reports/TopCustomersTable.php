<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Booking;
use App\Support\TenantContext;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TopCustomersTable extends TableWidget
{
    protected static ?string $heading = 'reports.widgets.top_customers';
    protected static bool $isLazy = true;

    protected function getTableQuery(): Builder
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return Booking::query()->whereRaw('1 = 0');
        }

        return Booking::query()
            ->select('customer_id')
            ->selectRaw('COUNT(*) as bookings_count')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total_spent')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderByDesc('bookings_count')
            ->with('customer')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('customer.full_name')
                ->label(__('bookings.booking.fields.customer'))
                ->getStateUsing(fn($record) => $record->customer?->full_name ?? '-')
                ->searchable(),

            Tables\Columns\TextColumn::make('bookings_count')
                ->label(__('reports.columns.bookings_count')),

            Tables\Columns\TextColumn::make('total_spent')
                ->label(__('reports.columns.total_spent'))
                ->formatStateUsing(fn($state) => number_format((float) $state, 2)),
        ];
    }

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return static::$heading ? __(static::$heading) : null;
    }

    public function getTableRecordKey(Model $record): string
    {
        return (string) ($record->customer_id ?? $record->id ?? spl_object_id($record));
    }
}

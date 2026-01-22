<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Invoice;
use App\Support\TenantContext;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class OverdueInvoicesTable extends TableWidget
{
    protected static ?string $heading = 'reports.widgets.overdue_invoices';
    protected static bool $isLazy = true;

    protected function getTableQuery(): Builder
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return Invoice::query()->whereRaw('1 = 0');
        }

        return Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'paid')
            ->orderBy('due_date');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('number')
                ->label(__('finance.invoices.fields.number'))
                ->searchable(),

            Tables\Columns\TextColumn::make('customer.full_name')
                ->label(__('finance.invoices.fields.customer'))
                ->searchable(),

            Tables\Columns\TextColumn::make('total')
                ->label(__('finance.invoices.fields.total'))
                ->formatStateUsing(fn($state) => number_format((float) $state, 2)),

            Tables\Columns\TextColumn::make('paid_amount')
                ->label(__('finance.invoices.fields.paid_amount'))
                ->formatStateUsing(fn($state) => number_format((float) $state, 2)),

            Tables\Columns\TextColumn::make('remaining')
                ->label(__('finance.invoices.fields.remaining'))
                ->formatStateUsing(fn($state) => number_format((float) $state, 2)),

            Tables\Columns\TextColumn::make('due_date')
                ->label(__('finance.invoices.fields.due_date'))
                ->date(),
        ];
    }

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return static::$heading ? __(static::$heading) : null;
    }
}

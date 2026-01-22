<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Invoice;
use App\Models\Payment;
use App\Support\TenantContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountingReportStats extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return [
                Stat::make(__('reports.stats.payments_in'), 0),
                Stat::make(__('reports.stats.payments_out'), 0),
                Stat::make(__('reports.stats.net_cash'), 0),
                Stat::make(__('reports.stats.overdue_invoices'), 0),
            ];
        }

        $from = now()->subDays(30)->toDateString();

        $paymentsIn = (float) Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('direction', 'in')
            ->whereDate('date', '>=', $from)
            ->sum('amount');

        $paymentsOut = (float) Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('direction', 'out')
            ->whereDate('date', '>=', $from)
            ->sum('amount');

        $overdue = (int) Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'paid')
            ->count();

        $netCash = $paymentsIn - $paymentsOut;

        return [
            Stat::make(__('reports.stats.payments_in'), number_format($paymentsIn, 2)),
            Stat::make(__('reports.stats.payments_out'), number_format($paymentsOut, 2)),
            Stat::make(__('reports.stats.net_cash'), number_format($netCash, 2)),
            Stat::make(__('reports.stats.overdue_invoices'), $overdue),
        ];
    }
}

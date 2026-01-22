<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Booking;
use App\Support\TenantContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingReportStats extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return [
                Stat::make(__('reports.stats.bookings_30'), 0),
                Stat::make(__('reports.stats.confirmed_30'), 0),
                Stat::make(__('reports.stats.cancelled_30'), 0),
                Stat::make(__('reports.stats.revenue_30'), 0),
            ];
        }

        $from = now()->subDays(30)->toDateString();

        $bookings = (int) Booking::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $from)
            ->count();

        $confirmed = (int) Booking::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $from)
            ->where('status', 'confirmed')
            ->count();

        $cancelled = (int) Booking::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $from)
            ->where('status', 'cancelled')
            ->count();

        $revenue = (float) Booking::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $from)
            ->sum('grand_total');

        return [
            Stat::make(__('reports.stats.bookings_30'), $bookings),
            Stat::make(__('reports.stats.confirmed_30'), $confirmed),
            Stat::make(__('reports.stats.cancelled_30'), $cancelled),
            Stat::make(__('reports.stats.revenue_30'), number_format($revenue, 2)),
        ];
    }
}

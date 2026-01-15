<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatsService;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStats extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()?->getKey()
            ?? Filament::auth()->user()?->tenant_id
            ?? 0;

        $counts = app(DashboardStatsService::class)->getCounts((int) $tenantId);

        return [
            Stat::make(__('Customers'), $counts['customers'] ?? 0),
            Stat::make(__('Bookings'), $counts['bookings'] ?? 0),
            Stat::make(__('Invoices'), $counts['invoices'] ?? 0),
            Stat::make(__('Unpaid Invoices'), $counts['unpaid'] ?? 0),
        ];
    }
}


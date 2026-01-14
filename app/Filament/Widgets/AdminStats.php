<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Booking;
use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStats extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()?->id ?? Filament::auth()->user()->tenant_id;

        $customers = Customer::query()->where('tenant_id', $tenantId)->count();
        $bookings  = Booking::query()->where('tenant_id', $tenantId)->count();
        $invoices  = Invoice::query()->where('tenant_id', $tenantId)->count();

        $unpaid = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        return [
            Stat::make('العملاء', $customers),
            Stat::make('الحجوزات', $bookings),
            Stat::make('الفواتير', $invoices),
            Stat::make('فواتير غير مكتملة', $unpaid),
        ];
    }
}

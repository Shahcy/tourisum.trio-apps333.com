<?php

namespace App\Filament\Portal\Widgets;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyQuickStats extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()?->id ?? Filament::auth()->user()->tenant_id;

        return [
            Stat::make(__('Customers'), Customer::query()->where('tenant_id', $tenantId)->count()),
            Stat::make(__('Bookings'), Booking::query()->where('tenant_id', $tenantId)->count()),
            Stat::make(__('Invoices'), Invoice::query()->where('tenant_id', $tenantId)->count()),
        ];
    }
}

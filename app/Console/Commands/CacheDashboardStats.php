<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheDashboardStats extends Command
{
    protected $signature = 'dashboard:cache';
    protected $description = 'Cache dashboard statistics per tenant';

    public function handle()
    {
        $tenants = Booking::select('tenant_id')->distinct()->pluck('tenant_id');

        foreach ($tenants as $tenantId) {

            $statuses = Booking::where('tenant_id', $tenantId)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $revenue = Payment::where('tenant_id', $tenantId)
                ->where('paid_at', '>=', now()->subDays(30))
                ->selectRaw('date(paid_at) as day, sum(amount) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray();

            Cache::put("dashboard:$tenantId:statuses", $statuses, 3600);
            Cache::put("dashboard:$tenantId:revenue", $revenue, 3600);
        }

        $this->info('Dashboard cached');
    }
}

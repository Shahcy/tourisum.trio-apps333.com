<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\DashboardStatsService;
use Illuminate\Console\Command;

class CacheDashboardStats extends Command
{
    protected $signature = 'dashboard:cache';

    protected $description = 'Cache dashboard statistics per tenant';

    public function handle(DashboardStatsService $service): void
    {
        $tenantIds = Tenant::query()->pluck('id');

        foreach ($tenantIds as $tenantId) {
            $service->refresh($tenantId);
        }

        $this->info('Dashboard cached');
    }
}

<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    public const COUNTS_TTL = 600;
    public const EXPIRATION_TTL = 3600;

    public function getCounts(int $tenantId): array
    {
        return Cache::remember(
            $this->countsKey($tenantId),
            self::COUNTS_TTL,
            fn () => $this->computeCounts($tenantId),
        );
    }

    public function refresh(int $tenantId): array
    {
        $counts = $this->computeCounts($tenantId);
        Cache::put($this->countsKey($tenantId), $counts, self::COUNTS_TTL);

        $statuses = $this->computeBookingStatuses($tenantId);
        Cache::put($this->statusesKey($tenantId), $statuses, self::EXPIRATION_TTL);

        $revenue = $this->computeRevenueSeries($tenantId);
        Cache::put($this->revenueKey($tenantId), $revenue, self::EXPIRATION_TTL);

        return compact('counts', 'statuses', 'revenue');
    }

    protected function countsKey(int $tenantId): string
    {
        return "dashboard:{$tenantId}:counts";
    }

    protected function statusesKey(int $tenantId): string
    {
        return "dashboard:{$tenantId}:statuses";
    }

    protected function revenueKey(int $tenantId): string
    {
        return "dashboard:{$tenantId}:revenue";
    }

    protected function computeCounts(int $tenantId): array
    {
        $customers = Customer::query()->where('tenant_id', $tenantId)->count();
        $bookings = Booking::query()->where('tenant_id', $tenantId)->count();
        $invoices = Invoice::query()->where('tenant_id', $tenantId)->count();

        $unpaid = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        return [
            'customers' => $customers,
            'bookings' => $bookings,
            'invoices' => $invoices,
            'unpaid' => $unpaid,
        ];
    }

    protected function computeBookingStatuses(int $tenantId): array
    {
        return Booking::query()
            ->where('tenant_id', $tenantId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    protected function computeRevenueSeries(int $tenantId): array
    {
        return Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('paid_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('date(paid_at) as day, sum(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();
    }
}

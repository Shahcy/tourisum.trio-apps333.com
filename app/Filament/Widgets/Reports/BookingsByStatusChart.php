<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Booking;
use App\Support\TenantContext;
use Filament\Widgets\ChartWidget;

class BookingsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'reports.widgets.bookings_by_status';
    protected static bool $isLazy = true;

    protected function getData(): array
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return [
                'datasets' => [[
                    'label' => __('Bookings'),
                    'data' => [],
                    'backgroundColor' => ['#f59e0b'],
                ]],
                'labels' => [],
            ];
        }

        $from = now()->subDays(30)->toDateString();
        $statuses = [
            'draft' => __('bookings.booking.statuses.draft'),
            'confirmed' => __('bookings.booking.statuses.confirmed'),
            'completed' => __('bookings.booking.statuses.completed'),
            'cancelled' => __('bookings.booking.statuses.cancelled'),
        ];

        $labels = [];
        $data = [];

        foreach ($statuses as $status => $label) {
            $count = (int) Booking::query()
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', $from)
                ->where('status', $status)
                ->count();

            $labels[] = $label;
            $data[] = $count;
        }

        return [
            'datasets' => [[
                'label' => __('Bookings'),
                'data' => $data,
                'backgroundColor' => ['#f59e0b', '#22c55e', '#3b82f6', '#ef4444'],
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string
    {
        return __(static::$heading ?? 'reports.widgets.bookings_by_status');
    }
}

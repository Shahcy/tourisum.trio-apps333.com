<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Support\TenantContext;
use Filament\Widgets\ChartWidget;

class BookingsStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Bookings by Status';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return [
                'datasets' => [['data' => [0, 0, 0, 0, 0, 0]]],
                'labels' => [__('Draft'), __('Quoted'), __('Confirmed'), __('Ticketed'), __('Completed'), __('Cancelled')],
            ];
        }

        $statuses = [
            'draft' => __('Draft'),
            'quoted' => __('Quoted'),
            'confirmed' => __('Confirmed'),
            'ticketed' => __('Ticketed'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
        ];

        $rows = Booking::query()
            ->where('tenant_id', $tenantId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $data = [];
        foreach ($statuses as $key => $label) {
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return [
            'datasets' => [
                ['data' => $data],
            ],
            'labels' => array_values($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string
    {
        return __('Bookings by Status');
    }
}

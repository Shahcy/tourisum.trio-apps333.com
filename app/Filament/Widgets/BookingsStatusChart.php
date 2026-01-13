<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class BookingsStatusChart extends ChartWidget
{
    protected static ?string $heading = 'الحجوزات حسب الحالة';

    protected static bool $isLazy = true;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $tenantId = Filament::auth()->user()->tenant_id;

        $statuses = [
            'draft' => 'Draft',
            'quoted' => 'Quoted',
            'confirmed' => 'Confirmed',
            'ticketed' => 'Ticketed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $rows = Booking::where('tenant_id', $tenantId)
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
                [
                    'data' => $data,
                ],
            ],
            'labels' => array_values($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

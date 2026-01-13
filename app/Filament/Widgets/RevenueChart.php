<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue';

    protected static bool $isLazy = true;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $tenantId = Filament::auth()->user()->tenant_id;

        $rows = Payment::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('date', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $data = [];

        foreach ($rows as $row) {
            $labels[] = $row->day;
            $data[] = (float) $row->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data,
                    'tension' => 0.4,
                    'borderWidth' => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }
    protected function getType(): string
    {
        return 'line';
    }
}

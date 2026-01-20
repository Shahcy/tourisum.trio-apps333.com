<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Support\TenantContext;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return [
                'datasets' => [[
                    'label' => __('Revenue'),
                    'data' => [],
                    'tension' => 0.4,
                    'borderWidth' => 3,
                ]],
                'labels' => [],
            ];
        }

        $rows = Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('direction', 'in')
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
            'datasets' => [[
                'label' => __('Revenue'),
                'data' => $data,
                'tension' => 0.4,
                'borderWidth' => 3,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string
    {
        return __('Monthly Revenue');
    }
}

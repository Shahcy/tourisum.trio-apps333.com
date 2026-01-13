<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStats;
use App\Filament\Widgets\AlertsList;
use App\Filament\Widgets\BookingsStatusChart;
use App\Filament\Widgets\RevenueChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getTitle(): string
    {
        return 'لوحة التحكم';
    }

    protected function getWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RevenueChart::class,
            BookingsStatusChart::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            AdminStats::class,
            AlertsList::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}

<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use App\Filament\Widgets\AdminStats;
use App\Filament\Widgets\AlertsList;
use App\Filament\Widgets\BookingsStatusChart;
use App\Filament\Widgets\RevenueChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use TranslatesPageAttributes;

    protected static ?string $title = 'Dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-home';

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

<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use App\Filament\Widgets\Reports\BookingReportStats;
use App\Filament\Widgets\Reports\BookingsByStatusChart;
use App\Filament\Widgets\Reports\LatestBookingsTable;
use App\Filament\Widgets\Reports\TopCustomersTable;
use Filament\Pages\Page;

class BookingReports extends Page
{
    use TranslatesPageAttributes;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'reports.group';
    protected static ?string $navigationLabel = 'reports.bookings';
    protected static ?string $title = 'reports.bookings';
    protected static ?int $navigationSort = 82;

    protected static string $view = 'filament.pages.reports.booking-reports';

    protected function getHeaderWidgets(): array
    {
        return [
            BookingReportStats::class,
            BookingsByStatusChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestBookingsTable::class,
            TopCustomersTable::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 2;
    }
}

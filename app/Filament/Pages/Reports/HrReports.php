<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use App\Filament\Widgets\Reports\HrReportStats;
use App\Filament\Widgets\Reports\PendingLeaveRequestsTable;
use App\Filament\Widgets\Reports\RecentAttendancesTable;
use Filament\Pages\Page;

class HrReports extends Page
{
    use TranslatesPageAttributes;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'reports.group';
    protected static ?string $navigationLabel = 'reports.hr';
    protected static ?string $title = 'reports.hr';
    protected static ?int $navigationSort = 81;

    protected static string $view = 'filament.pages.reports.hr-reports';

    protected function getHeaderWidgets(): array
    {
        return [
            HrReportStats::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    protected function getFooterWidgets(): array
    {
        return [
            RecentAttendancesTable::class,
            PendingLeaveRequestsTable::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 2;
    }
}

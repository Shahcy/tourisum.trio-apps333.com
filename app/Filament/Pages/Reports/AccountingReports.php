<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use App\Filament\Widgets\Reports\AccountingReportStats;
use App\Filament\Widgets\Reports\OverdueInvoicesTable;
use App\Filament\Widgets\Reports\RecentPaymentsTable;
use Filament\Pages\Page;

class AccountingReports extends Page
{
    use TranslatesPageAttributes;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'reports.group';
    protected static ?string $navigationLabel = 'reports.accounting';
    protected static ?string $title = 'reports.accounting';
    protected static ?int $navigationSort = 80;

    protected static string $view = 'filament.pages.reports.accounting-reports';

    protected function getHeaderWidgets(): array
    {
        return [
            AccountingReportStats::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 4;
    }

    protected function getFooterWidgets(): array
    {
        return [
            RecentPaymentsTable::class,
            OverdueInvoicesTable::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 2;
    }
}

<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use TranslatesPageAttributes;

    protected static ?string $title = 'Portal Dashboard';
    protected static ?string $navigationLabel = 'Portal Dashboard';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Portal\Widgets\CompanyQuickStats::class,
        ];
    }
}

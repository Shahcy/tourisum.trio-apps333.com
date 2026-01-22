<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class HrDashboard extends Page
{
    use TranslatesPageAttributes;

    protected static ?string $title = 'HR Dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'HR';
    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.pages.hr-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) (
            Gate::forUser($user)->check('page_HrDashboard')
            || $user?->hasAnyRole(['admin', 'super_admin'])
        );
    }
}

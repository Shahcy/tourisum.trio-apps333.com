<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;



class HrDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'HR';
    protected static ?string $title = 'الموارد البشرية';
    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 1;

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

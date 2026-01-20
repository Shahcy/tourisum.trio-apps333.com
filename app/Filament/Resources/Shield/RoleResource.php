<?php

namespace App\Filament\Resources\Shield;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RoleResource extends \BezhanSalleh\FilamentShield\Resources\RoleResource
{
    public static function isScopedToTenant(): bool
    {
        return false;
    }

    protected static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasRole('super_admin');
    }

    protected static function isRolesAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasAnyRole(['admin', 'company_admin', 'company admin', 'Company Admin']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! static::isSuperAdmin()) {
            $query->where('name', '!=', 'super_admin');
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return static::isSuperAdmin() || static::isRolesAdmin();
    }

    public static function canEdit($record): bool
    {
        if (static::isSuperAdmin()) {
            return true;
        }

        if ($record?->name === 'super_admin') {
            return false;
        }

        return static::isRolesAdmin();
    }

    public static function canDelete($record): bool
    {
        // لا حد يحذف super_admin
        if ($record?->name === 'super_admin') {
            return false;
        }

        // الحذف فقط للسوبر أدمن
        return static::isSuperAdmin();
    }
}

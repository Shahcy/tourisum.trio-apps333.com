<?php

namespace App\Filament\Resources\Concerns;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait ScopesToTenant
{
    protected static function isPortalPanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'portal';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // السوبرأدمن (admin panel): لا تقيد شيء
        if (! static::isPortalPanel()) {
            return $query;
        }

        /** @var User|null $u */
        $u = Auth::user();

        $tenantId = Filament::getTenant()?->id ?? $u?->tenant_id;

        return $query->where('tenant_id', $tenantId);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! static::isPortalPanel()) {
            return $data;
        }

        /** @var User|null $u */
        $u = Auth::user();

        $data['tenant_id'] = Filament::getTenant()?->id ?? $u?->tenant_id;

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (! static::isPortalPanel()) {
            return $data;
        }

        /** @var User|null $u */
        $u = Auth::user();

        $data['tenant_id'] = Filament::getTenant()?->id ?? $u?->tenant_id;

        return $data;
    }
}

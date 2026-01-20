<?php

namespace App\Filament\Resources\Concerns;

use App\Support\TenantContext;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

trait ScopesToTenant
{
    protected static function isPortalPanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'portal';
    }

    protected static function requireTenantId(): int
    {
        // Portal لازم يكون دائمًا ضمن Tenant (لوكل أو سيرفر)
        abort_if(! static::isPortalPanel(), 500, 'requireTenantId() called outside portal panel.');

        return TenantContext::requireId();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // admin panel: بدون تقييد
        if (! static::isPortalPanel()) {
            return $query;
        }

        return $query->where('tenant_id', static::requireTenantId());
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! static::isPortalPanel()) {
            return $data;
        }

        $data['tenant_id'] = static::requireTenantId();
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (! static::isPortalPanel()) {
            return $data;
        }

        $data['tenant_id'] = static::requireTenantId();
        return $data;
    }
}

<?php

namespace App\Support;

use App\Models\Tenant;
use App\Support\TenantManager;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    public static function current(): ?Tenant
    {
        $t = Filament::getTenant();
        if ($t instanceof Tenant) {
            return $t;
        }

        $t = TenantManager::current();
        if ($t instanceof Tenant) {
            return $t;
        }

        $u = Auth::user();
        return $u?->tenant ?? null;
    }

    public static function id(): ?int
    {
        return self::current()?->id;
    }

    public static function requireId(): int
    {
        $id = self::id();
        abort_if(! $id, 403, 'Tenant context is missing.');
        return $id;
    }
}

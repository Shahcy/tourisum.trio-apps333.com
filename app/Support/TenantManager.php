<?php

namespace App\Support;

use App\Models\Tenant;

class TenantManager
{
    protected static ?Tenant $tenant = null;

    public static function set(?Tenant $tenant): void
    {
        self::$tenant = $tenant;
    }

    public static function current(): ?Tenant
    {
        return self::$tenant;
    }

    public static function id(): ?int
    {
        return self::$tenant?->id;
    }

    public static function requireId(): int
    {
        $id = self::id();
        abort_if(! $id, 403, 'Tenant context is missing.');
        return $id;
    }
}

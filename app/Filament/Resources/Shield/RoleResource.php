<?php

namespace App\Filament\Resources\Shield;

class RoleResource extends \BezhanSalleh\FilamentShield\Resources\RoleResource
{
    public static function isScopedToTenant(): bool
    {
        return false;
    }
}

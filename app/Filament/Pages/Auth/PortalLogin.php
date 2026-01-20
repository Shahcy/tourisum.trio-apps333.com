<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login;

class PortalLogin extends Login
{
    protected function getRedirectUrl(): string
    {
        return '/portal';
    }
}

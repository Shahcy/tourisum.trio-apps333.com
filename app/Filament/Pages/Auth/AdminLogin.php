<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login;

class AdminLogin extends Login
{
    protected function getRedirectUrl(): string
    {
        return '/admin';
    }
}

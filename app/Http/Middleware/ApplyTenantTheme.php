<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;

class ApplyTenantTheme
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = TenantContext::current();
        $hex = $tenant?->primary_color;

        if (is_string($hex) && $hex !== '') {
            FilamentColor::register([
                'primary' => Color::hex($hex),
            ]);
        }

        return $next($request);
    }
}

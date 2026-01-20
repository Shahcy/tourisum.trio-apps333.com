<?php

namespace App\Providers\Filament;

use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $domain = config('app.domain');

        return $panel
            ->id('portal')
            ->path('portal')
            ->tenant(\App\Models\Tenant::class, slugAttribute: 'domain')
            ->authGuard('web')
            ->login(\App\Filament\Pages\Auth\PortalLogin::class)
            ->homeUrl('/portal')

            // Branding ديناميكي
            ->brandName(fn() => Filament::getTenant()?->name ?? config('app.name'))
            ->brandLogo(function () {
                $t = Filament::getTenant();
                if (! $t?->logo_path) {
                    return null;
                }
                return asset('storage/' . ltrim($t->logo_path, '/'));
            })
            ->brandLogoHeight('2.75rem')

            // Hook للـ brand لو بدك
            ->renderHook('panels::brand', fn() => view('filament.hooks.portal-brand', [
                'tenant' => Filament::getTenant(),
            ]))

            // أهم نقطة: الألوان لازم تتسجل “وقت الطلب” بعد تحديد التيننت
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\ApplyTenantTheme::class,
            ])
            ->assets([
                Css::make('portal-css', resource_path('css/portal.css')),
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Portal/Pages'), for: 'App\\Filament\\Portal\\Pages')
            ->discoverWidgets(in: app_path('Filament/Portal/Widgets'), for: 'App\\Filament\\Portal\\Widgets')

            ->userMenuItems([
                \Filament\Navigation\MenuItem::make('leave_impersonation')
                    ->label('Back to Super Admin')
                    ->url(fn() => route('admin.impersonate.leave'))
                    ->visible(fn() => (bool) session('impersonator_id')),
            ])

            ->navigationItems([
                NavigationItem::make('leave_impersonation_nav')
                    ->label('Back to Super Admin')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->url(fn() => route('admin.impersonate.leave'))
                    ->visible(fn() => (bool) session('impersonator_id')),
            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

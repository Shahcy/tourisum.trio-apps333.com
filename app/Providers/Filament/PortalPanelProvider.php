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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Css;

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $tenantResolver = fn() => Filament::getTenant();

        return $panel
            ->id('portal')
            ->path('portal')
            ->authGuard('web')
            ->login()
            ->tenant(Tenant::class)

            ->brandLogo(function () use ($tenantResolver) {
                $t = $tenantResolver();
                if (! $t?->logo_path) {
                    return null;
                }

                return asset('storage/' . ltrim($t->logo_path, '/'));
            })
            ->brandName(fn() => Filament::getTenant()?->name ?? config('app.name'))
            ->renderHook('panels::brand', fn() => view('filament.hooks.portal-brand'))

            ->brandLogoHeight('2.75rem')

            ->renderHook('panels::brand', function () use ($tenantResolver) {
                return view('filament.hooks.portal-brand', [
                    'tenant' => $tenantResolver(),
                ]);
            })

            ->colors([
                'primary' => $tenantResolver()?->primary_color ?? Color::Amber,
            ])

            ->assets([
                Css::make('portal-css', resource_path('css/portal.css')),
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Portal/Pages'), for: 'App\\Filament\\Portal\\Pages')
            ->discoverWidgets(in: app_path('Filament/Portal/Widgets'), for: 'App\\Filament\\Portal\\Widgets')

            ->userMenuItems([
                MenuItem::make('leave_impersonation')
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

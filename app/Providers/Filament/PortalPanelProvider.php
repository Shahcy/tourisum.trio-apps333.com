<?php

namespace App\Providers\Filament;

use App\Models\Tenant;
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

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('portal')
            ->path('portal')
            ->authGuard('web')
            ->login()

            ->tenant(Tenant::class)

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Portal/Pages'), for: 'App\\Filament\\Portal\\Pages')
            ->discoverWidgets(in: app_path('Filament/Portal/Widgets'), for: 'App\\Filament\\Portal\\Widgets')

            // 1) داخل قائمة المستخدم (أعلى يمين)
            ->userMenuItems([
                MenuItem::make('leave_impersonation')
                    ->label('Back to Super Admin')
                    ->url(fn() => route('admin.impersonate.leave'))
                    ->visible(fn() => (bool) session('impersonator_id')),
            ])

            // 2) كعنصر واضح في السايدبار
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

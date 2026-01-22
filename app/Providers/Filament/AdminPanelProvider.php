<?php

namespace App\Providers\Filament;

use App\Http\Middleware\ApplyTenantTheme;
use App\Http\Middleware\SetLocale;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $domain = config('app.domain');
        $adminSub = config('app.admin_subdomain');

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->login(\App\Filament\Pages\Auth\AdminLogin::class)
            ->homeUrl('/admin')
            ->brandName(null)
            ->brandLogo(null)

            ->renderHook('panels::body.end', fn() => view('filament.hooks.hide-company-permissions'))

            ->userMenuItems([
                \Filament\Navigation\MenuItem::make('switch_to_ar')
                    ->label('العربية')
                    ->url(fn() => route('lang.switch', ['locale' => 'ar']))
                    ->visible(fn() => app()->getLocale() !== 'ar'),

                \Filament\Navigation\MenuItem::make('switch_to_en')
                    ->label('English')
                    ->url(fn() => route('lang.switch', ['locale' => 'en']))
                    ->visible(fn() => app()->getLocale() !== 'en'),
            ])

            ->colors([
                'primary' => Color::Amber,
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')

            ->resources([
                \App\Filament\Resources\TenantResource::class,
                \App\Filament\Resources\UserResource::class,
            ])

            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->widgets([
                \App\Filament\Widgets\AdminStats::class,
                \App\Filament\Widgets\AlertsList::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                SetLocale::class,
                ApplyTenantTheme::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

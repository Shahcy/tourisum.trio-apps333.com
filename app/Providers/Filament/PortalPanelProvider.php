<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('portal')
            ->path('portal')
            ->authGuard('web')

            // Login فقط (بدون Register)
            ->login()

            // اعتمد على web middleware الافتراضي الخاص بالتطبيق
            // ولا تعيد تعريف EncryptCookies/StartSession/CSRF داخل Panel
            // لأن هذا يسبب تضارب و 419 و مشاكل Livewire

            ->discoverResources(in: app_path('Filament/Portal/Resources'), for: 'App\\Filament\\Portal\\Resources')
            ->discoverPages(in: app_path('Filament/Portal/Pages'), for: 'App\\Filament\\Portal\\Pages')
            ->discoverWidgets(in: app_path('Filament/Portal/Widgets'), for: 'App\\Filament\\Portal\\Widgets');
    }
}

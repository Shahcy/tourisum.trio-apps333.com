<?php

return [
    App\Providers\AppServiceProvider::class,

    // لتسجيل مسارات Livewire
    Livewire\LivewireServiceProvider::class,

    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\PortalPanelProvider::class,

    // مزوّد المسارات
    App\Providers\RouteServiceProvider::class,
];

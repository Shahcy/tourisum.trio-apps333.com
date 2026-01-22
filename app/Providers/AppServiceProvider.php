<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\InvoiceItem;
use App\Observers\InvoiceItemObserver;
use App\Models\Payment;
use App\Observers\PaymentObserver;
use App\Models\Booking;
use App\Observers\BookingObserver;
use App\Models\BookingPayment;
use App\Observers\BookingPaymentObserver;
use App\Models\Tenant;
use App\Observers\TenantObserver;
use Filament\Support\Facades\FilamentView;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return method_exists($user, 'hasRole') && $user->hasRole('super_admin')
                ? true
                : null;
        });

        InvoiceItem::observe(InvoiceItemObserver::class);
        Payment::observe(PaymentObserver::class);
        Booking::observe(BookingObserver::class);
        BookingPayment::observe(BookingPaymentObserver::class);

        // Auto-palette من اللوجو
        Tenant::observe(TenantObserver::class);

        // Branding ديناميكي للـ Portal (ألوان + شعار/اسم)
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn() => view('filament.hooks.tenant-branding-css')
        );

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::head.end',
            fn() => view('filament.hooks.brand-round-logo')
        );

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::topbar.start',
            fn() => view('filament.hooks.tenant-brand')
        );

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::sidebar.header',
            fn() => view('filament.hooks.app-brand')
        );

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::body.end',
            fn() => view('filament.hooks.ensure-livewire-start')
        );
    }
}

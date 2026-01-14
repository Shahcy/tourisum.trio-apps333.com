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

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
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
    }
}

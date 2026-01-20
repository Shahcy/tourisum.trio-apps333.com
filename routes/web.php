<?php

use App\Http\Controllers\Admin\ImpersonationController;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'ar'], true), 404);
    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');

Route::middleware(['web', 'auth'])->group(function () {
    // SuperAdmin -> O_OrU^U, UŸOœO_U.U+ O'OñUŸOc (Impersonation) O"O_U^U+ Login
    Route::get('/tenants/{tenant}/login-as-admin', function (Tenant $tenant) {
        abort_unless(Auth::user()?->canImpersonate(), 403);

        $super = Auth::user();
        session(['impersonator_id' => $super?->id]);

        $portalPanel = Filament::getPanel('portal');
        Filament::setCurrentPanel($portalPanel);
        Filament::setTenant($tenant);

        $target = User::where('tenant_id', $tenant->id)->orderBy('id')->firstOrFail();

        Auth::guard($portalPanel->getAuthGuard())->login($target);
        request()->session()->regenerate();

        return redirect()->route('filament.portal.pages.dashboard', ['tenant' => $tenant->id]);
    })->name('tenants.login-as-admin');

    Route::get('/admin/impersonate/{tenant}', [ImpersonationController::class, 'start'])
        ->name('admin.impersonate.start');

    Route::get('/admin/impersonate-leave', [ImpersonationController::class, 'leave'])
        ->name('admin.impersonate.leave');
});

// Livewire v3 required endpoints (explicit)
Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/livewire/update', $handle)->name('livewire.update');
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/livewire/livewire.js', $handle);
});

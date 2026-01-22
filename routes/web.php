<?php

use App\Http\Controllers\Admin\ImpersonationController;
use App\Models\Tenant;
use App\Models\User;
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
    Route::get('/tenants/{tenant}/login-as-admin', function (Tenant $tenant) {
        /** @var User|null $impersonator */
        $impersonator = Auth::user();
        abort_unless($impersonator?->canImpersonate(), 403);

        $targetUser = User::where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->firstOrFail();

        $impersonator->impersonate($targetUser);

        return redirect('/portal/' . $tenant->domain);
    })->name('tenants.login-as-admin');

    Route::get('/admin/impersonate/{tenant}', [ImpersonationController::class, 'start'])
        ->name('admin.impersonate.start');

    Route::get('/admin/impersonate-leave', [ImpersonationController::class, 'leave'])
        ->name('admin.impersonate.leave');
});

Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/livewire/update', $handle)->name('livewire.update');
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/livewire/livewire.js', $handle);
});
<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'ar'], true), 404);

    session(['locale' => $locale]);

    return redirect()->back();
})->name('lang.switch');

Route::middleware(['web', 'auth'])->group(function () {

    // SuperAdmin -> Login as company admin مباشرة
    Route::get('/admin/impersonate/{tenant}', function (Tenant $tenant) {

        /** @var \App\Models\User $super */
        $super = Auth::user();

        abort_unless($super->hasRole('super_admin'), 403);

        // اختَر أدمن الشركة
        $target = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'company_admin'))
            ->first();

        // fallback: أول مستخدم في الشركة
        if (! $target) {
            $target = User::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->first();
        }

        abort_unless($target, 404);

        // خزّن السوبرأدمن بالجلسة
        session(['impersonator_id' => $super->id]);

        // تسجيل دخول فعلي كـ target (هذا هو اللي يمنع صفحة login)
        Auth::login($target);

        // Redirect للداشبورد داخل tenant
        if (Route::has('filament.admin.pages.dashboard')) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        // fallback to the admin root if panel routes change
        return redirect('/admin');
    })->name('admin.impersonate');

    // Leave impersonation: رجوع للسوبرأدمن
    Route::get('/admin/impersonate/leave', function () {

        $impersonatorId = session('impersonator_id');

        abort_unless($impersonatorId, 403);

        session()->forget('impersonator_id');

        Auth::loginUsingId($impersonatorId);

        return redirect('/admin');
    })->name('admin.impersonate.leave');
});

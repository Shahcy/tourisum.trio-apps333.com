<?php

use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'ar'], true), 404);
    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');

// SuperAdmin -> دخول كأدمن شركة (Impersonation) بدون Login
Route::get('/tenants/{tenant}/login-as-admin', function (Tenant $tenant) {
    // إذا أردت حماية لاحقاً، ضع شرطك هنا بدلاً من middleware auth
    $super = User::first(); // عدّل هذا لاختيار المستخدم الذي تريد تسجيله كـ super
    session(['impersonator_id' => $super?->id]);

    $portalPanel = Filament::getPanel('portal');
    Filament::setCurrentPanel($portalPanel);
    Filament::setTenant($tenant);

    $target = User::where('tenant_id', $tenant->id)->orderBy('id')->firstOrFail();

    Auth::guard($portalPanel->getAuthGuard())->login($target);
    request()->session()->regenerate();

    return redirect()->route('filament.portal.pages.dashboard', ['tenant' => $tenant->id]);
})->name('tenants.login-as-admin');

// رجوع للمستخدم الأصلي
Route::get('/admin/impersonate/leave', function () {
    $superId = session('impersonator_id');
    session()->forget('impersonator_id');

    if ($superId) {
        $super = User::find($superId);
        if ($super) {
            Auth::guard('web')->login($super);
            request()->session()->regenerate();
        }
    }

    return redirect('/admin');
})->name('admin.impersonate.leave');

// اختصار من لوحة الأدمن إلى بوابة العميل
Route::get('/admin/impersonate/{tenant}', function (Tenant $tenant) {
    return redirect()->route('tenants.login-as-admin', ['tenant' => $tenant->id]);
})->name('admin.impersonate');

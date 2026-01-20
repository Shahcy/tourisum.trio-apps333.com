<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Tenant $tenant): RedirectResponse
    {
        $me = Auth::user();

        // حط تحقق بسيط (حسب عندك رول super_admin)
        // إذا عندك permissions بدل roles، بدّلها حسب نظامك
        abort_unless($me, 403);
        abort_unless($me->canImpersonate(), 403);

        // اختار المستخدم اللي رح ندخل فيه داخل الشركة:
        // 1) إذا عندك رول "admin" داخل التيننت
        // 2) أو أول مستخدم تابع للتيننت
        $target = User::query()
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) {
                // إذا User عندك فيه spatie roles
                if (method_exists(User::class, 'role')) {
                    $q->role('admin');
                }
            })
            ->first();

        if (! $target) {
            $target = User::query()->where('tenant_id', $tenant->id)->orderBy('id')->first();
        }

        abort_unless($target, 404, 'No tenant admin user found for this tenant.');

        // Impersonate
        $me->impersonate($target);

        // نرجع على البورتال مباشرة (لوكل path-based)
        return redirect('/portal/' . $tenant->domain);
    }

    public function leave(): RedirectResponse
    {
        $me = Auth::user();
        abort_unless($me, 403);

        $me->leaveImpersonation();

        return redirect('/admin');
    }
}

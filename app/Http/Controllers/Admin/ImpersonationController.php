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

        abort_unless($me, 403);
        abort_unless($me->canImpersonate(), 403);

        $target = User::query()
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) {
                if (method_exists(User::class, 'role')) {
                    $q->role('admin');
                }
            })
            ->first();

        if (! $target) {
            $target = User::query()->where('tenant_id', $tenant->id)->orderBy('id')->first();
        }

        abort_unless($target, 404, 'No tenant admin user found for this tenant.');

        $me->impersonate($target);

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


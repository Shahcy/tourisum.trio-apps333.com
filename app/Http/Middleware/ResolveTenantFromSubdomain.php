<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;

class ResolveTenantFromSubdomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $domain = config('app.domain');
        $adminSub = config('app.admin_subdomain');

        if ($adminSub && $domain && $host === "{$adminSub}.{$domain}") {
            TenantManager::set(null);
            return $next($request);
        }

        if (app()->environment('local') && in_array($host, [$domain, '127.0.0.1', 'localhost', '::1'], true)) {
            TenantManager::set(null);
            return $next($request);
        }

        if (! $domain || ! str_ends_with($host, $domain)) {
            abort(404);
        }

        $subdomain = trim(str_replace(".{$domain}", '', $host));

        if ($subdomain === '' || $subdomain === $adminSub) {
            abort(404);
        }

        if (in_array($subdomain, [$adminSub, 'www', 'api'], true)) {
            abort(404);
        }

        if (str_starts_with(ltrim($request->path(), '/'), 'admin')) {
            abort(404);
        }

        $tenant = Tenant::query()
            ->where('domain', $subdomain)
            ->first();

        abort_if(! $tenant, 404);

        TenantManager::set($tenant);

        return $next($request);
    }
}
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
        $host = $request->getHost(); // e.g. marwan.trio-apps.com
        $appDomain = config('app.domain'); // trio-apps.com
        $adminSubdomain = config('app.admin_subdomain'); // admin

        // U.O®OU,: admin.trio-apps.com
        if ($host === "{$adminSubdomain}.{$appDomain}") {
            TenantManager::set(null);
            return $next($request);
        }

        // Allow direct access to the app domain in local/dev without forcing a subdomain.
        if (app()->environment('local') && in_array($host, [$appDomain, '127.0.0.1', 'localhost', '::1'], true)) {
            TenantManager::set(null);
            return $next($request);
        }

        // U,OOýU. USU+O¦UØUS O"U? trio-apps.com
        if (! str_ends_with($host, $appDomain)) {
            abort(404);
        }

        // OO3O¦OrOñOOª subdomain: marwan U.U+ marwan.trio-apps.com
        $subdomain = trim(str_replace(".{$appDomain}", '', $host));

        // OOøO UŸOU+ O"O_U^U+ subdomain (U.O®U, trio-apps.com) O¦O1OU.U, O-O3O" OñO§O"O¦UŸ
        // UØU+O U+OrU,USUØ USOñU^O- U,OæU?O-Oc UØO"U^Oú OœU^ 404
        if ($subdomain === '' || $subdomain === $appDomain) {
            abort(404);
        }

        // O-U.OUSOc OOOU?USOc: U.U+O1 UŸU,U.OO¦ U.O-OªU^OýOc
        if (in_array($subdomain, [$adminSubdomain, 'www', 'api'])) {
            abort(404);
        }

        // Block admin panel paths when hitting tenant subdomains.
        if (str_starts_with(ltrim($request->path(), '/'), 'admin')) {
            abort(404);
        }

        // OªU,O" Tenant U.U+ DB: OU?O¦OñO OœU+ O1U+O_UŸ O1U.U^O_ "domain" OœU^ "slug"
        $tenant = Tenant::query()
            ->where('domain', $subdomain) // O1U+O_UŸ O3OO"U,U<O domain U.O®U, marwan-toursim
            ->first();

        abort_if(! $tenant, 404);

        TenantManager::set($tenant);

        return $next($request);
    }
}

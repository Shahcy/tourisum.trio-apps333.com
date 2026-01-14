<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $default = config('app.locale', 'en');

        // لا تلمس session إلا إذا كانت موجودة ومُفعّلة
        if ($request->hasSession()) {
            $locale = $request->session()->get('locale', $default);
        } else {
            $locale = $default;
        }

        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : $default;

        app()->setLocale($locale);

        return $next($request);
    }
}

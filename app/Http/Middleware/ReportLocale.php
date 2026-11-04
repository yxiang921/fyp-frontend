<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ReportLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->input('lang', $request->hasSession() ? $request->session()->get('locale', 'en') : $request->header('Accept-Language', 'en'));
        if (!in_array($locale, ['en', 'ms', 'zh'], true)) $locale = 'en';
        app()->setLocale($locale);
        if ($request->hasSession()) $request->session()->put('locale', $locale);
        return $next($request);
    }
}

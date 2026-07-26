<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('locales.supported', ['id' => []]));
        $locale = $request->session()->get('locale', config('app.locale'));

        $locale = in_array($locale, $supported, true) ? $locale : config('app.fallback_locale');
        $nativeLocale = config("locales.supported.{$locale}.native") ? $locale : 'en';
        app()->setLocale($nativeLocale);
        $request->attributes->set('display_locale', $locale);

        return $next($request);
    }
}

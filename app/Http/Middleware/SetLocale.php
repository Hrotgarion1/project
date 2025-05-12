<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['ca', 'de', 'en', 'es', 'eu', 'fr', 'gl', 'it', 'pt', 'ru'];
           $locale = session('locale', config('app.locale'));

           if (in_array($locale, $supportedLocales)) {
               app()->setLocale($locale);
           } else {
               app()->setLocale(config('app.fallback_locale'));
           }

           return $next($request);
    }
}

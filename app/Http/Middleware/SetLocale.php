<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     * Reads the locale from the session (set by the language toggle route)
     * and applies it to the current request lifecycle.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale', 'en'));

        // Only allow supported locales
        if (!in_array($locale, ['en', 'id'])) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}

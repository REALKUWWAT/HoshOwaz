<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
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
        $availableLocales = ['tk', 'en', 'ru', 'tr'];
        $locale = Session::get('locale', config('app.locale', 'tk'));

        if (!in_array($locale, $availableLocales)) {
            $locale = 'tk';
        }

        App::setLocale($locale);

        return $next($request);
    }
}


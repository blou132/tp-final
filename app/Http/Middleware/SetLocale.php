<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'fr'];
        $locale = config('app.locale');

        if ($request->hasSession()) {
            if ($request->has('lang') && in_array($request->query('lang'), $supportedLocales, true)) {
                $request->session()->put('locale', $request->query('lang'));
            }

            $locale = $request->session()->get('locale', config('app.locale'));
        }

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}

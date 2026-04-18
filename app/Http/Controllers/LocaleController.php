<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['en', 'fr'], true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        $redirect = $request->query('redirect');

        if (is_string($redirect) && str_starts_with($redirect, '/')) {
            return redirect()->to($redirect);
        }

        $referer = $request->headers->get('referer');

        if (is_string($referer) && Str::startsWith($referer, $request->getSchemeAndHttpHost())) {
            $path = parse_url($referer, PHP_URL_PATH) ?: '/';
            $query = parse_url($referer, PHP_URL_QUERY);

            if (is_string($path) && ! preg_match('#^/locale/(en|fr)$#', $path)) {
                $target = $path;

                if (is_string($query) && $query !== '') {
                    $target .= '?'.$query;
                }

                return redirect()->to($target);
            }
        }

        return redirect()->route('home');
    }
}

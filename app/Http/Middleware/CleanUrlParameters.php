<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanUrlParameters
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $query = $request->query();

        if (! is_array($query) || $query === []) {
            return $next($request);
        }

        $cleanedQuery = collect($query)
            ->except(['v', 'lang'])
            ->all();

        if ($cleanedQuery === $query) {
            return $next($request);
        }

        return $this->redirectToCleanUrl($request, $cleanedQuery);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    protected function redirectToCleanUrl(Request $request, array $query): RedirectResponse
    {
        $target = $request->url();

        if ($query !== []) {
            $target .= '?'.http_build_query($query);
        }

        return redirect()->to($target);
    }
}

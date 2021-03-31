<?php

namespace Depiedra\LaravelGettext\Middleware;

class Language
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, \Closure $next)
    {
        laravel_gettext()->getLocale();

        return $next($request);
    }
}
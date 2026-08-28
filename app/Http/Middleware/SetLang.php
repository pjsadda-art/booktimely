<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(file_exists(storage_path() . "/installed"))
        {
            \App::setLocale(getActiveLanguage());
        }
        $input = $request->all();
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars_decode($value);
                $value = preg_replace('/<\s*script\b[^>]*>(.*?)<\s*\/\s*script\s*>/is', '', $value);
                $value = str_replace(['&lt;', '&gt;', 'javascript','alert'], '', $value);
            }
        });
        $request->merge($input);

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class APILog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        Log::channel('API_log')->info(' *********************************** API START *********************************** ');
        Log::channel('API_log')->info('API URL');
        Log::channel('API_log')->info($request->url());
        Log::channel('API_log')->info('request');
        Log::channel('API_log')->info($request);
        Log::channel('API_log')->info(PHP_EOL);
        Log::channel('API_log')->info('response');
        Log::channel('API_log')->info($response);
        Log::channel('API_log')->info(' *********************************** API END *********************************** ');
    }
}

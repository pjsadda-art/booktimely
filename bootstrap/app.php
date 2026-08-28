<?php

use App\Http\Middleware\CustomApiAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLang;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'APILog' => \App\Http\Middleware\APILog::class,
            'PlanModuleCheck' => \App\Http\Middleware\PlanModuleCheck::class,
            'jwt.api.auth' => CustomApiAuth::class,
            '2fa' => \App\Http\Middleware\EnsureTwoFactorVerified::class,
        ]);
        // Append middleware to the 'web' group
        $middleware->appendToGroup('web', SetLang::class);
        // Exclude specific routes from CSRF protection
        $middleware->validateCsrfTokens(
            except: ['plan-get-paytm-status',
                    '/iyzipay/*',
                    '/aamarpay/*',
                    '/appointment/iyzipay/status/*',
                    '/easebuzz/*',
                    '/powertranz/plan/payment/status',
                    '/powertranz/appointment/payment/status/*',
                    '/sslcommerz/*',
                    'appointments/{slug}/{appointment?}',
                    'appointment-duration',
                    'appointment-book',
                    'proposal/pdf/{id}',
                    'reply'
                    ] // Add your routes here
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

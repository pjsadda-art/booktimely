<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\Module;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('module', function ($app) {
            return new Module();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set debug mode based on client IP
        $clientIp = $this->getClientIp();
        $debugIps = config('app.debug_ips', ['106.219.69.113']);
        
        if (in_array($clientIp, $debugIps)) {
            config(['app.debug' => true]);
        } else {
            config(['app.debug' => false]);
        }
    }

    /**
     * Get the client's IP address
     */
    private function getClientIp(): string
    {
        $ip = '127.0.0.1';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple IPs (take the first one)
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}

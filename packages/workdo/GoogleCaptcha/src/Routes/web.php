<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Workdo\GoogleCaptcha\Http\Controllers\GoogleCaptchaController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:GoogleCaptcha'])->group(function () {
    Route::prefix('googlecaptcha')->group(function () {
        Route::post('/recaptcha-settings/store', [GoogleCaptchaController::class,'setting'])->name('recaptcha.setting.store');
    });
});
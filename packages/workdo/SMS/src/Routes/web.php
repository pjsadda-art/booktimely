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
use Workdo\SMS\Http\Controllers\Company\SettingsController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:SMS'])->group(function () {
    Route::prefix('sms')->group(function () {
        Route::post('sms-settings/store', [SettingsController::class, 'store'])->name('sms.setting.save');
        Route::post('get-smsfields', [SettingsController::class, 'get_smsfields'])->name('get.sms.fields');
    });
});
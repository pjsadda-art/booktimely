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
use Workdo\Paypal\Http\Controllers\PaypalController;

Route::prefix('paypal')->middleware('web')->group(function () {

    Route::group(['middleware' =>  ['auth', 'verified']], function () {
        // Setting
        Route::post('/setting/store', [PaypalController::class, 'setting'])->name('paypal.setting.store')->middleware(['PlanModuleCheck:Paypal']);

        // Plan
        Route::post('plan-pay-with/paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal');
        Route::get('plan-get-paypal-status/{plan_id}', [PaypalController::class, 'planGetPaypalStatus'])->name('plan.get.paypal.status');
    });

    // Appointment
    Route::post('/appointment-pay-with-paypal', [PaypalController::class, 'appointmentPayWithPaypal'])->name('appointment.pay.with.paypal');
    Route::get('/appointment/paypal/status/{slug}', [PaypalController::class, 'getAppointmentPaymentStatus'])->name('appointment.paypal.status');
});

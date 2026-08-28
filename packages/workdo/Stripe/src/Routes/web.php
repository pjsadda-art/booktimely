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
use Workdo\Stripe\Http\Controllers\StripeController;

Route::prefix('stripe')->middleware('web')->group(function () {

    Route::middleware(['auth', 'verified'])->group(function () {
        // Setting
        Route::post('/setting/store', [StripeController::class, 'settingConfig'])->name('stripe.setting.store')->middleware('PlanModuleCheck:Stripe');

        // Plan
        Route::post('plan-pay-with/stripe', [StripeController::class, 'planPayWithStripe'])->name('plan.pay.with.stripe');
        Route::get('plan-get-payment-status/', [StripeController::class, 'planGetStripeStatus'])->name('plan.get.payment.status');
    });

    // Appointment
    Route::post('appointment-pay-with-stripe', [StripeController::class, 'appointmentPayWithStripe'])->name('appointment.pay.with.stripe');
    Route::get('appointment-pay-stripe-status/{slug}', [StripeController::class, 'getAppointmentPaymentStatus'])->name('appointment.stripe.status');
});

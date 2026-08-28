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
use Workdo\ServiceTax\Http\Controllers\ServiceTaxController;
use Workdo\ServiceTax\Http\Controllers\Company\SettingsController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:ServiceTax'])->group(function () {
    Route::prefix('servicetax')->group(function() {
        Route::get('/', [ServiceTaxController::class, 'index'])->name('servicetax.index');
        Route::get('/create', [ServiceTaxController::class, 'create'])->name('servicetax.create');
        Route::post('/store', [ServiceTaxController::class, 'store'])->name('servicetax.store');
        Route::get('/edit/{id}', [ServiceTaxController::class, 'edit'])->name('servicetax.edit');
        Route::post('/update/{id}', [ServiceTaxController::class, 'update'])->name('servicetax.update');
        Route::get('/delete/{id}', [ServiceTaxController::class, 'destroy'])->name('servicetax.delete');
        Route::get('/show/{id}', [ServiceTaxController::class, 'show'])->name('servicetax.show');
        Route::post('service-tax-settings-save', [SettingsController::class, 'settingSave'])->name('servicetax.setting.save');
    });
});

Route::middleware(['web'])->group(function () {
    Route::post('/apply/servicetax', [ServiceTaxController::class, 'applyServiceTax'])->name('apply.servicetax');
});

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
use Workdo\PromoCodes\Http\Controllers\PromoCodesController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:PromoCodes'])->group(function () {
    Route::prefix('promocodes')->group(function() {
        Route::get('/', [PromoCodesController::class, 'index'])->name('promocode.index');
        Route::get('/create', [PromoCodesController::class, 'create'])->name('promocode.create');
        Route::post('/store', [PromoCodesController::class, 'store'])->name('promocode.store');
        Route::get('/edit/{id}', [PromoCodesController::class, 'edit'])->name('promocode.edit');
        Route::post('/update/{id}', [PromoCodesController::class, 'update'])->name('promocode.update');
        Route::get('/delete/{id}', [PromoCodesController::class, 'destroy'])->name('promocode.delete');
        Route::get('/show/{id}', [PromoCodesController::class, 'show'])->name('promocode.show');
    });
});

Route::middleware(['web'])->group(function () {
    Route::post('/apply/promocode', [PromoCodesController::class, 'applyPromoCode'])->name('apply.promocode');
});

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
use Workdo\FlexibleDays\Http\Controllers\FlexibleDaysController;
use Workdo\FlexibleDays\Http\Controllers\FlexileDayBreaksController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:FlexibleDays'])->group(function () {
    Route::prefix('flexibledays')->group(function () {
        Route::get('/flexibledays-create/{id}', [FlexibleDaysController::class, 'create'])->name('flexibledays.create');
        Route::post('/flexibledays-store', [FlexibleDaysController::class, 'store'])->name('flexibledays.store');
        Route::post('/flexibledays-delete', [FlexibleDaysController::class, 'flexibleDaysDelete'])->name('flexibledays.delete');

        Route::get('flexibleday-breaks/create',[FlexileDayBreaksController::class,'create'])->name('flexibleday.breaks.create');
        Route::post('flexibleday-breaks/store',[FlexileDayBreaksController::class,'store'])->name('flexibleday.breaks.store');
        Route::get('flexibleday-breaks/edit/{id}',[FlexileDayBreaksController::class,'edit'])->name('flexibleday.breaks.edit');
        Route::get('flexibleday-breaks/destroy/{id}',[FlexileDayBreaksController::class,'destroy'])->name('flexibleday.breaks.destroy');
    });
});

Route::post('flexibledays-dayoffcheck', [FlexibleDaysController::class, 'dayOffCheck'])->name('flexibledays.dayoffcheck');

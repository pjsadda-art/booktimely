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
use Workdo\Api\Http\Controllers\ApiGeneratorController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:Api'])->group(function () {
    Route::get('/api-index',[ApiGeneratorController::class,'index'])->name('api.index');
});




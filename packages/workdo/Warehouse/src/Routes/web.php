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
use Workdo\Warehouse\Http\Controllers\WarehouseController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:Warehouse'])->group(function () {
    Route::prefix('warehouse')->group(function() {
        Route::get('/', [WarehouseController::class, 'index'])->name('warehouse.index');
        Route::get('/create', [WarehouseController::class, 'create'])->name('warehouse.create');
        Route::post('/store', [WarehouseController::class, 'store'])->name('warehouse.store');
        Route::get('/edit/{id}', [WarehouseController::class, 'edit'])->name('warehouse.edit');
        Route::post('/update/{id}', [WarehouseController::class, 'update'])->name('warehouse.update');
        Route::get('/delete/{id}', [WarehouseController::class, 'destroy'])->name('warehouse.delete');
        Route::get('/show/{id}', [WarehouseController::class, 'show'])->name('warehouse.show');
    });
});

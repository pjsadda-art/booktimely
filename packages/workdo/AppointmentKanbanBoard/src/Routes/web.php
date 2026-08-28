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
use Workdo\AppointmentKanbanBoard\Http\Controllers\AppointmentKanbanBoardController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:AppointmentKanbanBoard'])->group(function () {
    Route::resource('Appointment-Kanban-Board', AppointmentKanbanBoardController::class);

    Route::post('/appointment/update-order', [AppointmentKanbanBoardController::class, 'orderUpdate'])->name('appointment.updateOrder');

});
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
use Workdo\CollaborativeServices\Http\Controllers\CollaborativeServicesController;

Route::prefix('collaborativeservices')->group(function () {
    Route::get('/check', [CollaborativeServicesController::class, 'checkCollaborativeService'])->name('check.collaborative.service');
});

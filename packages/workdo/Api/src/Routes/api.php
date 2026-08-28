<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Workdo\Api\Http\Controllers\AppointmentApiController;
use Workdo\Api\Http\Controllers\AuthController;
use Workdo\Api\Http\Controllers\BusinessApiController;
use Workdo\Api\Http\Controllers\CategoryApiController;
use Workdo\Api\Http\Controllers\ContactApiController;
use Workdo\Api\Http\Controllers\CustomerApiController;
use Workdo\Api\Http\Controllers\CustomStatusApiController;
use Workdo\Api\Http\Controllers\DashboardApiController;
use Workdo\Api\Http\Controllers\LocationApiController;
use Workdo\Api\Http\Controllers\ServiceApiController;
use Workdo\Api\Http\Controllers\StaffApiController;
use Workdo\Api\Http\Controllers\SubscriberApiController;
use Workdo\Api\Http\Controllers\UserApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['custom.jwt'])->get('/api', function (Request $request) {
    return $request->user();
});
Route::post('/auth/login',[AuthController::class,'login']);
Route::post('/auth/register',[AuthController::class,'register']);
Route::post('/auth/logout',[AuthController::class,'logout']);
Route::post('/auth/change/password', [AuthController::class, 'changePassword']);

Route::middleware(['custom.jwt'])->group(function () {

    Route::get('/home', [DashboardApiController::class, 'dashboard']);
    Route::post('/update-profile', [DashboardApiController::class, 'updateProfile']);
    Route::get('/business/list', [BusinessApiController::class, 'index']);
    Route::get('business/edit/{id}',[BusinessApiController::class,'edit']);
    Route::put('business/update', [BusinessApiController::class, 'update']);
    Route::delete('business/delete', [BusinessApiController::class, 'destroy']);
    Route::put('business/change/{id}',[BusinessApiController::class,'BusinessChange']);

    Route::prefix('{slug}')->group(function () {
        Route::get('/users',[UserApiController::class,'index']);
        Route::post('user/store',[UserApiController::class,'store']);
        Route::get('user/edit/{id}',[UserApiController::class,'edit']);
        Route::put('user/update/{id}',[UserApiController::class,'update']);
        Route::delete('user/delete/{id}',[UserApiController::class,'destroy']);
        Route::post('user-password-reset/{id}', [UserApiController::class, 'userPasswordReset']);

        Route::get('appointment/list', [AppointmentApiController::class, 'index']);
        Route::post('appointment/store', [AppointmentApiController::class, 'store']);
        Route::put('appointment/update/{id}', [AppointmentApiController::class, 'update']);
        Route::delete('appointment/delete/{id}', [AppointmentApiController::class, 'destroy']);
        Route::get('appointment/detail/{id}', [AppointmentApiController::class, 'appoitmentDetail']);
        Route::put('appointment-status-change', [AppointmentApiController::class, 'AppointmentStatusChange']);
        Route::get('appointment-status', [AppointmentApiController::class, 'AppointmentStatusList']);
        Route::get('custom-status', [CustomStatusApiController::class, 'index']);
        Route::post('custom-status-store', [CustomStatusApiController::class, 'store']);
        Route::put('custom-status-update/{id}', [CustomStatusApiController::class, 'update']);
        Route::delete('custom-status-delete/{id}', [CustomStatusApiController::class, 'destroy']);

        Route::get('service/list', [ServiceApiController::class, 'index']);
        Route::post('service-store', [ServiceApiController::class, 'store']);
        Route::put('service-update/{id}', [ServiceApiController::class, 'update']);
        Route::delete('service-delete/{id}', [ServiceApiController::class, 'destroy']);

        Route::get('staff-list', [StaffApiController::class, 'index']);
        Route::post('staff-store', [StaffApiController::class, 'store']);
        Route::put('staff-update/{id}', [StaffApiController::class, 'update']);
        Route::delete('staff-delete/{id}', [StaffApiController::class, 'destroy']);

        Route::get('location-list', [LocationApiController::class, 'index']);
        Route::post('location-store', [LocationApiController::class, 'store']);
        Route::put('location-update/{id}', [LocationApiController::class, 'update']);
        Route::delete('location-delete/{id}', [LocationApiController::class, 'destroy']);

        Route::get('category-list', [CategoryApiController::class, 'index']);
        Route::post('category-store', [CategoryApiController::class, 'store']);
        Route::put('category-update/{id}', [CategoryApiController::class, 'update']);
        Route::delete('category-delete/{id}', [CategoryApiController::class, 'destroy']);

        Route::get('contact-list', [ContactApiController::class, 'index']);
        Route::post('contact-store', [ContactApiController::class, 'store']);
        Route::delete('contact-delete/{id}', [ContactApiController::class, 'destroy']);

        Route::get('customer-list', [CustomerApiController::class, 'index']);
        Route::post('customer-store', [CustomerApiController::class, 'store']);
        Route::put('customer-update/{id}', [CustomerApiController::class, 'update']);
        Route::delete('customer-delete/{id}', [CustomerApiController::class, 'destroy']);

        Route::get('Subscriber-list', [SubscriberApiController::class, 'index']);
        Route::post('Subscriber-store', [SubscriberApiController::class, 'store']);
        Route::delete('Subscriber-delete/{id}', [SubscriberApiController::class, 'destroy']);

    }); 
});
    
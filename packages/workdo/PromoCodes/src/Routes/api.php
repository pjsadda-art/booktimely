<?php

use Illuminate\Http\Request;
use Workdo\PromoCodes\Http\Controllers\Api\PromocodeApiController;

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

Route::middleware('auth:api')->get('/promocodes', function (Request $request) {
    return $request->user();
});

Route::prefix('PromoCodes')->group(function () {
    Route::middleware(['jwt.api.auth'])->group(function () {
        Route::get('promocodes',[PromocodeApiController::class,'index']);
        Route::post('promocodes-store',[PromocodeApiController::class,'store']);
        Route::put('promocodes/update/{id}',[PromocodeApiController::class,'update']);
        Route::delete('promocodes/delete/{id}',[PromocodeApiController::class,'destroy']);
        Route::get('promocodes/details/{id}',[PromocodeApiController::class,'promocodeDetail']);
    });
});
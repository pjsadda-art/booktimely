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
use Workdo\Purchases\Http\Controllers\PurchaseController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:Purchases'])->group(function () {
        Route::resource('purchases', PurchaseController::class)->except(['create']);
        Route::get('purchases-grid', [PurchaseController::class, 'grid'])->name('purchases.grid');
        Route::post('purchases/items', [PurchaseController::class, 'items'])->name('purchases.items');
        Route::get('purchases/{id}/payment', [PurchaseController::class, 'payment'])->name('purchases.payment');
        Route::post('purchases/{id}/payment/store', [PurchaseController::class, 'createPayment'])->name('purchases.payment.store');
        Route::post('purchases/{id}/payment/{pid}/destroy', [PurchaseController::class, 'paymentDestroy'])->name('purchases.payment.destroy');

        Route::post('purchases/product/destroy', [PurchaseController::class, 'productDestroy'])->name('purchases.product.destroy');
        Route::post('purchases/vender', [PurchaseController::class, 'vender'])->name('purchases.vender');
        Route::post('purchases/product', [PurchaseController::class, 'product'])->name('purchases.product');
        Route::get('purchases/create/{cid}', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::get('purchases/{id}/sent', [PurchaseController::class, 'sent'])->name('purchases.sent');
        Route::get('purchases/{id}/resent', [PurchaseController::class, 'resent'])->name('purchases.resent');
        Route::post('purchase/{id}/file', [PurchaseController::class, 'fileUpload'])->name('purchases.files.upload')->middleware(['auth']);
        Route::delete("purchase/{id}/destroy", [PurchaseController::class, 'fileUploadDestroy'])->name("purchases.attachment.destroy")->middleware(['auth']);
      

});

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
use Workdo\Invoice\Http\Controllers\InvoiceController;
use Workdo\Invoice\Http\Controllers\InvoicePayTypeController;
use App\Http\Controllers\BanktransferController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:Purchases'])->group(function () {
        Route::post('invoice/customer', [InvoiceController::class, 'customer'])->name('invoice.customer');
        Route::post('invoice-attechment/{id}', [InvoiceController::class, 'invoiceAttechment'])->name('invoice.file.upload');
        Route::delete('invoice-attechment/destroy/{id}', [InvoiceController::class, 'invoiceAttechmentDestroy'])->name('invoice.attachment.destroy');
        Route::post('invoice/product', [InvoiceController::class, 'product'])->name('invoice.product');
        Route::get('invoice/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoice.duplicate');
        Route::get('invoice/{id}/recurring', [InvoiceController::class, 'recurring'])->name('invoice.recurring');
        Route::get('invoice/items', [InvoiceController::class, 'items'])->name('invoice.items');
        Route::post('invoice/product/destroy', [InvoiceController::class, 'productDestroy'])->name('invoice.product.destroy');
        Route::get('invoice/grid/view', [InvoiceController::class, 'Grid'])->name('invoice.grid.view');
        Route::resource('invoice', InvoiceController::class)->except(['create']);
        Route::get('invoice/create/{cid}', [InvoiceController::class, 'create'])->name('invoice.create');
        Route::get('invoice/{id}/sent', [InvoiceController::class, 'sent'])->name('invoice.sent');
        Route::get('invoice/{id}/resent', [InvoiceController::class, 'resent'])->name('invoice.resent');
        Route::get('invoice/{id}/payment/reminder', [InvoiceController::class, 'paymentReminder'])->name('invoice.payment.reminder');
        Route::get('invoice/pdf/{id}', [InvoiceController::class, 'invoice'])->name('invoice.pdf');
        Route::get('invoice/{id}/payment', [InvoiceController::class, 'payment'])->name('invoice.payment');
        Route::post('invoice/{id}/payment/store', [InvoiceController::class, 'createPayment'])->name('invoice.payment.store');
        Route::get('invoice/{id}/payment/{pid}/edit', [InvoiceController::class, 'paymentEdit'])->name('invoice.payment.edit');
        Route::post('invoice/{id}/payment/{pid}/update', [InvoiceController::class, 'paymentUpdate'])->name('invoice.payment.update');
        Route::post('invoice/{id}/payment/{pid}/', [InvoiceController::class, 'paymentDestroy'])->name('invoice.payment.destroy');
        Route::get('invoice/{id}/send', [InvoiceController::class, 'customerInvoiceSend'])->name('invoice.send');
        Route::post('invoice/{id}/send/mail', [InvoiceController::class, 'customerInvoiceSendMail'])->name('invoice.send.mail');
        Route::post('invoice/section/type', [InvoiceController::class, 'InvoiceSectionGet'])->name('invoice.section.type');
        Route::get('delivery-form/pdf/{id}', [InvoiceController::class, 'pdf'])->name('delivery-form.pdf');
        Route::get('invoice/{id}/convert/appointment', [InvoiceController::class, 'convertToInvoice'])->name('invoice.convert.appointment');
        Route::post('/get-invoice-customers', [InvoiceController::class, 'getInvoiceCustomers'])->name('invoice.customers');
        Route::get('/search-invoice-customers', [InvoiceController::class, 'searchInvoiceCustomers'])->name('invoice.customers.search');

        Route::post('invoice-item-detail', [InvoiceController::class, 'getInvoicItemeDetail'])->name('newspaper.invoice.item.details');

        Route::post('invoice/course', [InvoiceController::class, 'course'])->name('invoice.course');
        Route::get('invoice/status/view', [InvoiceController::class, 'InvocieStatus'])->name('invoice.status.view');

        Route::resource('invoice-pay-type', InvoicePayTypeController::class)->except(['show']);

});

Route::middleware(['web'])->group(function () {
        Route::get('/invoice/pay/{invoice}', [InvoiceController::class, 'payinvoice'])->name('pay.invoice');
        Route::post('/invoice-pay-with-bank', [BanktransferController::class, 'invoicePayWithBank'])->name('invoice.pay.with.bank');
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\DepositApiController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\IndustryController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [ApiController::class, 'login'])->middleware(['APILog']);
Route::get('/dashboard', [ApiController::class, 'dashboard'])->middleware(['auth:sanctum','APILog']);
Route::get('/business-list', [ApiController::class, 'getBusinessList'])->middleware(['auth:sanctum','APILog']);
Route::get('/appointment-list', [ApiController::class, 'getAppointmentList'])->middleware(['auth:sanctum','APILog']);
Route::post('/change-business', [ApiController::class, 'changeBusiness'])->middleware(['auth:sanctum','APILog']);
Route::post('/edit-business', [ApiController::class, 'editBusiness'])->middleware(['auth:sanctum','APILog']);
Route::post('/delete-business', [ApiController::class, 'deleteBusiness'])->middleware(['auth:sanctum','APILog']);
Route::post('/edit-profile', [ApiController::class, 'editProfile'])->middleware(['auth:sanctum','APILog']);
Route::post('/change-password', [ApiController::class, 'changePassword'])->middleware(['auth:sanctum','APILog']);
Route::get('/appointment-status-list', [ApiController::class, 'getAppointmentStatusList'])->middleware(['auth:sanctum','APILog']);

// Super Admin - Industry Management
Route::get('/admin/industries', [IndustryController::class, 'adminIndex'])->middleware(['auth:sanctum','APILog']);
Route::post('/admin/industries', [IndustryController::class, 'adminStore'])->middleware(['auth:sanctum','APILog']);
Route::put('/admin/industries/{id}', [IndustryController::class, 'adminUpdate'])->middleware(['auth:sanctum','APILog']);
Route::delete('/admin/industries/{id}', [IndustryController::class, 'adminDestroy'])->middleware(['auth:sanctum','APILog']);

// Tenant - Industry selection
Route::get('/tenant/industry', [IndustryController::class, 'tenantShow'])->middleware(['auth:sanctum','APILog']);
Route::put('/tenant/industry', [IndustryController::class, 'tenantUpdate'])->middleware(['auth:sanctum','APILog']);
Route::post('/change-appontment-status', [ApiController::class, 'changeAppointmentStatus'])->middleware(['auth:sanctum','APILog']);
Route::post('/logout', [ApiController::class, 'logout'])->middleware(['APILog']);
Route::get('/service-list', [ApiController::class, 'getServiceList'])->middleware(['auth:sanctum','APILog']);
Route::post('/create-service', [ApiController::class, 'createService'])->middleware(['auth:sanctum','APILog']);
Route::post('/edit-service', [ApiController::class, 'editService'])->middleware(['auth:sanctum','APILog']);
Route::post('/delete-service', [ApiController::class, 'deleteService'])->middleware(['auth:sanctum','APILog']);
Route::post('/delete-appointment', [ApiController::class, 'deleteAppointment'])->middleware(['auth:sanctum','APILog']);
Route::get('/custom-status-list', [ApiController::class, 'getCustomStatusList'])->middleware(['auth:sanctum','APILog']);
Route::post('/create-custom-status', [ApiController::class, 'createCustomStatus'])->middleware(['auth:sanctum','APILog']);
Route::post('/edit-custom-status', [ApiController::class, 'editCustomStatus'])->middleware(['auth:sanctum','APILog']);
Route::post('/delete-custom-status', [ApiController::class, 'deleteCustomStatus'])->middleware(['auth:sanctum','APILog']);
Route::post('/update-fcm-token', [ApiController::class, 'updateFcmToken'])->middleware(['auth:sanctum','APILog']);
Route::get('/staff-list', [ApiController::class, 'getStaffList'])->middleware(['auth:sanctum','APILog']);
Route::get('/get-slots', [ApiController::class, 'getSlots'])->middleware(['auth:sanctum','APILog']);
Route::get('/location-list', [ApiController::class, 'getLocationList'])->middleware(['auth:sanctum','APILog']);
Route::get('/category-list', [ApiController::class, 'getCategoryList'])->middleware(['auth:sanctum','APILog']);
Route::post('/create-appointment', [ApiController::class, 'createAppointment'])->middleware(['auth:sanctum','APILog']);
Route::post('/edit-appointment', [ApiController::class, 'editAppointment'])->middleware(['auth:sanctum','APILog']);

// Deposits
Route::get('/appointments/{id}/deposit', [DepositApiController::class, 'show'])->middleware(['auth:sanctum','APILog']);
Route::post('/appointments/{id}/request-deposit', [DepositApiController::class, 'requestDeposit'])->middleware(['auth:sanctum','APILog']);
// Counter payments. Not gated on Stripe/PayPal being configured — a salon
// without a gateway is exactly the one that depends on this.
Route::post('/appointments/{id}/deposit/manual-payment', [DepositApiController::class, 'manualDepositPayment'])->middleware(['auth:sanctum','APILog']);
Route::post('/appointments/{id}/forfeit-deposit', [DepositApiController::class, 'forfeit'])->middleware(['auth:sanctum','APILog']);
Route::post('/appointments/{id}/refund-deposit', [DepositApiController::class, 'refund'])->middleware(['auth:sanctum','APILog']);

// Gateways call this server-to-server, so it carries no session. Authorised by
// the invoice token in the body instead, compared in constant time.
Route::post('/deposits/{appointmentId}/payment-callback', [DepositApiController::class, 'paymentCallback'])->middleware(['APILog']);

// WhatsApp Cloud API webhook. Meta calls this server-to-server — authorised by
// its own verify token (GET, one-time setup) and X-Hub-Signature-256 HMAC
// (POST, every delivery), never by session or Sanctum.
Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify'])->middleware(['APILog']);
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'receive'])->middleware(['APILog']);




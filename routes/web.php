<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BanktransferController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Company\SettingsController as CompanySettingsController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use App\Http\Controllers\SuperAdmin\IndustryController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BookingV2Controller;
use App\Http\Controllers\StaffRosterController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\CustomerDuplicateController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DepositCheckoutController;
use App\Http\Controllers\WhatsAppChatController;
use App\Http\Controllers\Inventory\ProductController as InventoryProductController;
use App\Http\Controllers\Inventory\ReferenceController as InventoryReferenceController;
use App\Http\Controllers\Inventory\StockController as InventoryStockController;
use App\Http\Controllers\Inventory\PurchaseController as InventoryPurchaseController;
use App\Http\Controllers\Inventory\InternalUseController as InventoryInternalUseController;
use App\Http\Controllers\Inventory\ReportController as InventoryReportController;
use App\Http\Controllers\BusinessHoursController;
use App\Http\Controllers\BusinessHolidayController;
use App\Http\Controllers\CustomStatusController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\ThemeSettingController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\SubscribeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Route::get('appointments/{slug}/{appointment?}', [AppointmentController::class, 'appointmentForm'])->name('appointments.form');
Route::any('appointments/{slug}/{appointment?}',[AppointmentController::class,'appointmentForm'])->name('appointments.form');
Route::post('appointment-book', [AppointmentController::class, 'appointmentFormSubmit'])->name('appointment.form.submit');

// The one customer-facing route that asks for money. Public by design: the
// invoice token is the credential, and every feature that needs a customer to
// pay links here rather than at a staff screen they cannot open.
Route::get('deposit/pay/{token}', [DepositCheckoutController::class, 'show'])->name('deposit.pay');
Route::post('deposit/pay/{token}/{gateway}', [DepositCheckoutController::class, 'start'])->name('deposit.pay.start');
Route::get('deposit/pay/{token}/{gateway}/return', [DepositCheckoutController::class, 'complete'])->name('deposit.pay.complete');
Route::get('appointments/{slug}/{id}', [AppointmentController::class, 'appointmentDone'])->name('appointments.done');
Route::post('appointment-duration', [AppointmentController::class, 'appointmentDuration'])->name('appointment.duration');
Route::post('service-duration', [AppointmentController::class, 'serviceDuration'])->name('service.duration');
Route::get('get-staff-data', [StaffController::class, 'getStaffData'])->name('get.staff.data');
Route::get('appointment/rtl', [AppointmentController::class, 'appointmentRtlSetting'])->name('appointment.rtl');
Route::post('check-user-data', [AppointmentController::class, 'checkUser'])->name('check.user.data');
Route::get('get-services-by-category', [StaffController::class, 'getServicesByCategory'])->name('get.services.by.category');
Route::resource('contacts', ContactUsController::class);
Route::get('/contacts/{id}/description', [ContactUsController::class,'description'])->name('contact.description');
Route::resource('subscribes', SubscribeController::class);

// for checking online appointment for theme
Route::get('check-service-online-meeting/{businessSlug}', [ServiceController::class, 'checkServiceOnlineMeeting'])->name('check.service.online.meeting');

// for checking online appointment for form layout
Route::get('check-service-online-meeting-form-layout/{businessSlug}', [ServiceController::class, 'checkServiceOnlineMeetingFormLayout'])->name('check.service.online.meeting');

// Auth::routes();
require __DIR__ . '/auth.php';

Route::get('/register/{lang?}', [RegisteredUserController::class, 'create'])->name('register');
Route::get('/login/{lang?}', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::get('/forgot-password/{lang?}', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::get('/verify-email/{lang?}', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');

// module page before login
Route::post('reply', [HomeController::class, 'reply'])->name('webhook.reply');

Route::get('add-on', [HomeController::class, 'Software'])->name('apps.software');
Route::get('add-on/details/{slug}', [HomeController::class, 'SoftwareDetails'])->name('software.details');
Route::get('pricing', [HomeController::class, 'Pricing'])->name('apps.pricing');
Route::get('/', [HomeController::class, 'index'])->name('start');

Route::middleware(['auth'])->group(function () {
    Route::get('2fa/challenge', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'create'])->name('2fa.challenge');
    Route::post('2fa/challenge', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'store'])->name('2fa.verify')->middleware('throttle:5,1');
    Route::get('2fa/setup', [\App\Http\Controllers\TwoFactorAuthenticationController::class, 'setup'])->name('2fa.setup');
    Route::post('2fa/setup', [\App\Http\Controllers\TwoFactorAuthenticationController::class, 'setup'])->name('2fa.setup.post');
    Route::post('2fa/confirm', [\App\Http\Controllers\TwoFactorAuthenticationController::class, 'confirm'])->name('2fa.confirm')->middleware('throttle:5,1');
});

Route::middleware(['auth', 'verified', '2fa'])->group(function () {

    //Role & Permission
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);



    //dashbord
    // if (module_is_active('GoogleAuthentication')) {
    //     Route::get('/dashboard', [HomeController::class, 'Dashboard'])->name('dashboard')->middleware(
    //         [
    //             '2fa',
    //         ]
    //     );
    //     Route::get('/home', [HomeController::class, 'Dashboard'])->name('home')->middleware(
    //         [
    //             '2fa',
    //         ]
    //     );
    //     Route::get('/appointment-dashboard/{staff?}', [HomeController::class, 'AppointmentDashboard'])->name('appointment.dashboard')->middleware(
    //         [
    //             '2fa',
    //         ]
    //     );

    // } else {
        Route::get('/dashboard', [HomeController::class, 'Dashboard'])->name('dashboard');
        Route::get('/appointment-dashboard/{staff?}', [HomeController::class, 'AppointmentDashboard'])->name('appointment.dashboard');
        Route::any('dashboard-index', [HomeController::class, 'Dashboard'])->name('dashboard.index');
        Route::get('/home', [HomeController::class, 'Dashboard'])->name('home');
    // }
    Route::any('dashboard-index', [HomeController::class, 'Dashboard'])->name('dashboard.index');
    // settings
    Route::resource('settings', SettingsController::class);
    Route::post('settings-save', [CompanySettingsController::class, 'store'])->name('settings.save');
    Route::post('company/settings-save', [CompanySettingsController::class, 'store'])->name('company.settings.save');
    Route::post('super-admin/settings-save', [SuperAdminSettingsController::class, 'store'])->name('super.admin.settings.save');
    Route::post('super-admin/system-settings-save', [SuperAdminSettingsController::class, 'SystemStore'])->name('super.admin.system.setting.store');
    Route::post('company/system-settings-save', [CompanySettingsController::class, 'SystemStore'])->name('company.system.setting.store');
    Route::post('comapny-currency-settings', [CompanySettingsController::class, 'saveCompanyCurrencySettings'])->name('company.setting.currency.settings');

    Route::post('currency-settings', [SuperAdminSettingsController::class, 'saveCurrencySettings'])->name('super.admin.currency.settings');

    Route::post('company-setting-save', [CompanySettingsController::class, 'companySettingStore'])->name('company.setting.save');
    Route::post('company/week-settings-save', [CompanySettingsController::class, 'weekStore'])->name('company.week.setting.store');
    Route::post('/update-note-value', [SuperAdminSettingsController::class, 'updateNoteValue'])->name('admin.update.note.value');
    Route::post('company/update-note-value', [CompanySettingsController::class, 'companyupdateNoteValue'])->name('company.update.note.value');

    Route::post('company/custom-js-save', [CompanySettingsController::class, 'CustomJsStore'])->name('company.custom.js.store');
    Route::post('company/custom-css-save', [CompanySettingsController::class, 'CustomCssStore'])->name('company.custom.css.store');
    Route::post('company/default-status-save', [CompanySettingsController::class, 'DefaultStatusStore'])->name('company.default.status.store');
    Route::post('company/deposit-settings-save', [CompanySettingsController::class, 'depositSettingsStore'])->name('company.deposit.settings.store');
    Route::post('company/whatsapp-settings-save', [CompanySettingsController::class, 'whatsappSettingsStore'])->name('company.whatsapp.settings.store');

    Route::post('company/booking-mode-save', [CompanySettingsController::class, 'bookingModeStore'])->name('company.booking.mode.store');

    Route::post('email-settings-save', [SettingsController::class, 'mailStore'])->name('email.setting.store');
    Route::post('test-mail', [SettingsController::class, 'testMail'])->name('test.mail');
    Route::post('test-mail-send', [SettingsController::class, 'sendTestMail'])->name('test.mail.send');
    Route::post('email-notification-settings-save', [SettingsController::class, 'mailNotificationStore'])->name('email.notification.setting.store');

    Route::post('storage-settings-save', [SuperAdminSettingsController::class, 'storageStore'])->name('storage.setting.store');
    Route::post('seo/setting/save', [SuperAdminSettingsController::class, 'seoSetting'])->name('seo.setting.save');

    Route::get('/setting/section/{module}/{methord?}', [SettingsController::class, 'getSettingSection'])->name('setting.section.get');

    // bank-transfer
    Route::resource('bank-transfer-request', BanktransferController::class);
    Route::post('bank-transfer-setting', [BanktransferController::class, 'setting'])->name('bank.transfer.setting');
    Route::post('/bank/transfer/pay', [BanktransferController::class, 'planPayWithBank'])->name('plan.pay.with.bank');

    //users
    Route::resource('users', UserController::class);
    Route::get('users/list/view', [UserController::class, 'List'])->name('users.list.view');
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::post('edit-profile', [UserController::class, 'editprofile'])->name('edit.profile');
    Route::post('change-password', [UserController::class, 'updatePassword'])->name('update.password');
    Route::any('user-reset-password/{id}', [UserController::class, 'UserPassword'])->name('users.reset');
    Route::get('user-login/{id}', [UserController::class, 'LoginManage'])->name('users.login');
    Route::post('user-reset-password/{id}', [UserController::class, 'UserPasswordReset'])->name('user.password.update');
    Route::get('users/{id}/login-with-company', [UserController::class, 'LoginWithCompany'])->name('login.with.company');
    Route::get('company-info/{id}', [UserController::class, 'CompnayInfo'])->name('company.info');
    Route::post('user-unable', [UserController::class, 'UserUnable'])->name('user.unable');
    Route::get('business-links/{id}', [UserController::class, 'BusinessLinks'])->name('business.links');
    Route::get('user-verified/{id}', [UserController::class, 'verifeduser'])->name('user.verified');

    //User Log
    Route::get('users/logs/history', [UserController::class, 'UserLogHistory'])->name('users.userlog.history');
    Route::get('users/logs/{id}', [UserController::class, 'UserLogView'])->name('users.userlog.view');
    Route::delete('users/logs/destroy/{id}', [UserController::class, 'UserLogDestroy'])->name('users.userlog.destroy');


    // Two-Factor Authentication (profile self-service)
    Route::get('profile/2fa/status', [\App\Http\Controllers\TwoFactorAuthenticationController::class, 'status'])->name('2fa.status');
    Route::post('profile/2fa/disable', [\App\Http\Controllers\TwoFactorAuthenticationController::class, 'disable'])->name('2fa.disable');
    Route::post('profile/2fa/recovery-codes', [\App\Http\Controllers\TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])->name('2fa.recovery-codes');

    // Two-Factor Authentication (Super Admin management)
    Route::get('super-admin/users/2fa', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'index'])->name('super.admin.2fa.index');
    Route::post('super-admin/users/{user}/2fa/require', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'require'])->name('super.admin.2fa.require');
    Route::post('super-admin/users/{user}/2fa/disable', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'disable'])->name('super.admin.2fa.disable');
    Route::post('super-admin/users/{user}/2fa/reset', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'reset'])->name('super.admin.2fa.reset');

    // Two-Factor Authentication (company-wide policy)
    Route::post('company/2fa-settings-save', [\App\Http\Controllers\CompanyTwoFactorSettingsController::class, 'store'])->name('company.2fa.settings.save');

    // impersonating
    Route::get('login-with-company/exit', [UserController::class, 'ExitCompany'])->name('exit.company');

    // Language
    Route::get('/lang/change/{lang}', [LanguageController::class, 'changeLang'])->name('lang.change');
    Route::get('langmanage/{lang?}/{module?}', [LanguageController::class, 'index'])->name('lang.index');
    Route::get('create-language', [LanguageController::class, 'create'])->name('create.language');
    Route::post('langs/{lang?}/{module?}', [LanguageController::class, 'storeData'])->name('lang.store.data');
    Route::post('disable-language', [LanguageController::class, 'disableLang'])->name('disablelanguage');
    Route::any('store-language', [LanguageController::class, 'store'])->name('store.language');
    Route::delete('/lang/{id}', [LanguageController::class, 'destroy'])->name('lang.destroy');
    Route::get('export/lang/json',[LanguageController::class,'exportLangJson'])->name('export.lang.json');
    Route::get('import/lang/json/upload',[LanguageController::class,'importLangJsonUpload'])->name('import.lang.json.upload');
    Route::post('import/lang/json',[LanguageController::class,'importLangJson'])->name('import.lang.json');
    // End Language

    // location
    Route::resource('location', LocationController::class);
    // End location

    // category
    Route::resource('category', CategoryController::class);
    // End category

    // Super Admin - Industry Management (System Settings)
    Route::get('super-admin/industries', [IndustryController::class, 'index'])->name('super.admin.industries.index');
    Route::post('super-admin/industries', [IndustryController::class, 'store'])->name('super.admin.industries.store');
    Route::put('super-admin/industries/{industry}', [IndustryController::class, 'update'])->name('super.admin.industries.update');
    Route::post('super-admin/industries/{industry}/toggle-status', [IndustryController::class, 'toggleStatus'])->name('super.admin.industries.toggle');
    Route::delete('super-admin/industries/{industry}', [IndustryController::class, 'destroy'])->name('super.admin.industries.destroy');
    // End Industry Management

    // Tenant - Business Profile industry change
    Route::post('business/industry-update', [BusinessController::class, 'updateIndustry'])->name('business.industry.update');
    // End tenant industry

    // service
    Route::resource('service', ServiceController::class);
    // End service

    // service
    Route::resource('staff', StaffController::class);
    // End service

    // appointment
    Route::resource('appointment', AppointmentController::class);

    Route::post('appointment/list', [AppointmentController::class, 'index'])->name('appointment.list.index');

    Route::get('appointment-calendar', function () {
        return redirect()->route('bookings-v2.calendar', request()->query(), 301);
    })->name('appointment.calendar');
    Route::get('appointment-details/{id}', [AppointmentController::class, 'appointmentDetails'])->name('appointment.details');


    // Booking V2 calendar (display) — resourceTimeGridDay with split resource/event feeds
    Route::get('bookings-v2/calendar', [BookingV2Controller::class, 'calendar'])->name('bookings-v2.calendar');
    Route::get('bookings-v2/staffs', [BookingV2Controller::class, 'staffs'])->name('bookings-v2.staffs');
    Route::get('bookings-v2/events', [BookingV2Controller::class, 'events'])->name('bookings-v2.events');
    Route::get('bookings-v2/form-data', [BookingV2Controller::class, 'formData'])->name('bookings-v2.form-data');
    Route::get('bookings-v2/customers', [BookingV2Controller::class, 'searchCustomers'])->name('bookings-v2.customers');
    Route::get('bookings-v2/customers/{id}/history', [BookingV2Controller::class, 'customerAppointments'])->name('bookings-v2.customer.history');
    Route::get('bookings-v2/appointments/{id}', [BookingV2Controller::class, 'getAppointment'])->name('bookings-v2.appointment');
    Route::post('bookings-v2/appointments', [BookingV2Controller::class, 'storeAppointments'])->name('bookings-v2.appointment.store');
    Route::put('bookings-v2/appointments/{id}', [BookingV2Controller::class, 'updateAppointment'])->name('bookings-v2.appointment.update');
    Route::post('bookings-v2/appointments/{id}/move', [BookingV2Controller::class, 'moveAppointment'])->name('bookings-v2.appointment.move');

    // WhatsApp Chat tab
    Route::get('bookings-v2/appointments/{id}/whatsapp/messages', [WhatsAppChatController::class, 'messages'])->name('bookings-v2.whatsapp.messages');
    Route::post('bookings-v2/appointments/{id}/whatsapp/send', [WhatsAppChatController::class, 'send'])->name('bookings-v2.whatsapp.send');
    // End Booking V2 calendar

    // Staff Roster — weekly grid of continuous / end-dated / casual shifts
    Route::get('staff-roster', [StaffRosterController::class, 'index'])->name('staff-roster.index');
    Route::get('staff-roster/conflicts', [StaffRosterController::class, 'conflicts'])->name('staff-roster.conflicts');
    Route::get('staff-roster/grid', [StaffRosterController::class, 'grid'])->name('staff-roster.grid');
    Route::get('staff-roster/shifts/resolve', [StaffRosterController::class, 'resolveShift'])->name('staff-roster.shifts.resolve');
    Route::post('staff-roster/shifts', [StaffRosterController::class, 'store'])->name('staff-roster.shifts.store');
    Route::put('staff-roster/shifts/{id}', [StaffRosterController::class, 'update'])->name('staff-roster.shifts.update');
    Route::delete('staff-roster/shifts/{id}', [StaffRosterController::class, 'destroy'])->name('staff-roster.shifts.destroy');
    // End Staff Roster

    // Job Cards — scanned paper job card attached to an appointment
    Route::get('appointment/{appointment}/job-card', [JobCardController::class, 'show'])->name('job-card.show');
    Route::post('appointment/{appointment}/job-card', [JobCardController::class, 'store'])->name('job-card.store');
    Route::delete('job-card-file/{id}', [JobCardController::class, 'destroyFile'])->name('job-card.file.destroy');
    // End Job Cards

    Route::get('appointment-status-change/{id}', [AppointmentController::class, 'appointmentStatusChange'])->name('appointment.status.change');
    Route::post('appointment-status-update/{id}', [AppointmentController::class, 'appointmentStatusUpdate'])->name('appointment.status.update');

    Route::post('appointment-attachment-destroy/{id}', [AppointmentController::class, 'appointmentAttachmentDelete'])->name('appointment.attachment.destroy');
    // End appointment

    // custom field
    Route::post('business/custom-field-setting/{id}', [CustomFieldController::class, 'CustomFieldSetting'])->name('custom-field.setting');
    Route::post('/delete-field', [CustomFieldController::class, 'destroy'])->name('delete.field');
    // End custom field

    // custom status
    Route::resource('custom-status', CustomStatusController::class);
    // End custom status

    // Files
    Route::post('business/files-setting/{id}', [FileController::class, 'Filesetting'])->name('files.setting');
    // End Files

    // customer
    // Declared before the resource: `customer/{customer}` would otherwise swallow
    // `customer/duplicates` and try to look up a customer with that id.
    Route::get('customer/duplicates', [CustomerDuplicateController::class, 'index'])->name('customer.duplicates');
    Route::get('customer/duplicates/merge', [CustomerDuplicateController::class, 'mergeForm'])->name('customer.merge.form');
    Route::post('customer/duplicates/merge', [CustomerDuplicateController::class, 'merge'])->name('customer.merge');

    Route::resource('customer', CustomerController::class);
    Route::get('customer-list', [CustomerController::class, 'customerList'])->name('customer.list');
    Route::any('customer-ajax-create', [CustomerController::class, 'customerAjaxCreate'])->name('customer.ajax.create');
    Route::post('customer-ajax-submit', [CustomerController::class, 'customerAjaxSubmit'])->name('customer.ajax.submit');
    Route::get('/api/customers/search', [CustomerController::class, 'search'])->name('customers.search');

    // Customer profile — tabbed panels, each loaded in isolation
    Route::get('customer/{customer}/profile', [CustomerProfileController::class, 'show'])->name('customer.profile');
    Route::post('customer/{customer}/note', [CustomerProfileController::class, 'storeNote'])->name('customer.note.store');
    Route::post('customer/{customer}/risk', [CustomerProfileController::class, 'toggleRisk'])->name('customer.toggle-risk');
    Route::post('customer/{customer}/communication', [CustomerProfileController::class, 'updateCommunication'])->name('customer.communication.update');
    // End customer

    // Deposits — every action operates on the booking group, not one row
    Route::get('deposit/{id}/request', [DepositController::class, 'create'])->name('deposit.create');
    Route::post('deposit/{id}/request', [DepositController::class, 'store'])->name('deposit.store');
    Route::get('deposit/{id}/payment', [DepositController::class, 'paymentForm'])->name('deposit.payment-form');
    Route::post('deposit/{id}/payment', [DepositController::class, 'payOnSpot'])->name('deposit.pay-on-spot');
    Route::get('deposit/{id}/forfeit', [DepositController::class, 'forfeitForm'])->name('deposit.forfeit-form');
    Route::post('deposit/{id}/forfeit', [DepositController::class, 'forfeit'])->name('deposit.forfeit');
    Route::get('deposit/{id}/refund', [DepositController::class, 'refundForm'])->name('deposit.refund-form');
    Route::post('deposit/{id}/refund', [DepositController::class, 'refund'])->name('deposit.refund');
    Route::post('deposit/{id}/resend', [DepositController::class, 'resend'])->name('deposit.resend');
    Route::post('deposit/{id}/regenerate', [DepositController::class, 'regenerate'])->name('deposit.regenerate');
    // End deposits

    // Inventory V2 — self-contained; no dependency on customers or deposits
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('products', [InventoryProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [InventoryProductController::class, 'create'])->name('products.create');
        Route::post('products', [InventoryProductController::class, 'store'])->name('products.store');
        Route::get('products/{id}', [InventoryProductController::class, 'show'])->name('products.show');
        Route::get('products/{id}/edit', [InventoryProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{id}', [InventoryProductController::class, 'update'])->name('products.update');
        Route::delete('products/{id}', [InventoryProductController::class, 'destroy'])->name('products.destroy');
        Route::post('products/{id}/rebuild', [InventoryProductController::class, 'rebuild'])->name('products.rebuild');

        Route::get('stock', [InventoryStockController::class, 'index'])->name('stock.index');
        Route::get('stock/{action}/new', [InventoryStockController::class, 'form'])->name('stock.form');
        Route::post('stock/opening', [InventoryStockController::class, 'openingBalance'])->name('stock.opening');
        Route::post('stock/adjust', [InventoryStockController::class, 'adjust'])->name('stock.adjust');
        Route::post('stock/transfer', [InventoryStockController::class, 'transfer'])->name('stock.transfer');

        Route::get('purchases', [InventoryPurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/create', [InventoryPurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases', [InventoryPurchaseController::class, 'store'])->name('purchases.store');
        Route::get('purchases/{id}', [InventoryPurchaseController::class, 'show'])->name('purchases.show');
        Route::post('purchases/{id}/confirm', [InventoryPurchaseController::class, 'confirm'])->name('purchases.confirm');
        Route::post('purchases/{id}/receive', [InventoryPurchaseController::class, 'receive'])->name('purchases.receive');
        Route::delete('purchases/{id}', [InventoryPurchaseController::class, 'destroy'])->name('purchases.destroy');

        Route::get('internal-use', [InventoryInternalUseController::class, 'index'])->name('internal-use.index');
        Route::post('internal-use/open', [InventoryInternalUseController::class, 'open'])->name('internal-use.open');
        Route::post('internal-use/{id}/close', [InventoryInternalUseController::class, 'close'])->name('internal-use.close');

        Route::get('reports/summary', [InventoryReportController::class, 'summary'])->name('reports.summary');
        Route::get('reports/ledger', [InventoryReportController::class, 'ledger'])->name('reports.ledger');
        Route::get('reports/valuation', [InventoryReportController::class, 'valuation'])->name('reports.valuation');

        // One controller drives all seven lookup tables; {type} selects which.
        Route::get('{type}', [InventoryReferenceController::class, 'index'])->name('reference.index');
        Route::post('{type}', [InventoryReferenceController::class, 'store'])->name('reference.store');
        Route::put('{type}/{id}', [InventoryReferenceController::class, 'update'])->name('reference.update');
        Route::delete('{type}/{id}', [InventoryReferenceController::class, 'destroy'])->name('reference.destroy');
    });
    // End Inventory V2

    // business hours
    Route::resource('business-hours', BusinessHoursController::class);
    // End business hours

    // business hours
    Route::resource('business-holiday', BusinessHolidayController::class);
    // End business hours

    // business
    Route::resource('business', BusinessController::class);
    Route::get('manage/business/', [BusinessController::class, 'ManageBusiness'])->name('manage.business');
    Route::post('business/theme/update', [BusinessController::class, 'BusinessThemeUpdate'])->name('business.theme.update');
    Route::get('business/{id}/manage', [BusinessController::class, 'businessManage'])->name('business.manage');
    Route::get('business/change/{id}', [BusinessController::class, 'change'])->name('business.change');
    Route::post('business/check', [BusinessController::class, 'businessCheck'])->name('business.check');
    Route::post('business/domain-setting/{id}', [BusinessController::class, 'domainsetting'])->name('business.domain-setting');
    Route::post('business/slot-capacity-setting/{id}', [BusinessController::class, 'slotCapacitysetting'])->name('slot.capacity-setting');
    Route::post('business/appointment-reminder-setting/{id}', [BusinessController::class, 'appointmentRemindersetting'])->name('appointment.reminder-setting');
    Route::post('business/slot-interval-setting/{id}', [BusinessController::class, 'slotIntervalSetting'])->name('slot.interval-setting');
    // end business

    // theme customize
    Route::get('themes/{id}/customize/{business}', [ThemeSettingController::class, 'themeCustomize'])->name('business.customize');
    Route::get('themes/{id}/customize/{slug}/{sub_slug}/{business}', [ThemeSettingController::class, 'customize_theme'])->name('customize.edit');
    Route::post('themes/{business}/{id}/customize', [ThemeSettingController::class, 'customize_theme_update'])->name('customize.update');
    Route::post('file-get', [ThemeSettingController::class, 'imageFileGet'])->name('file.get');
    // end theme customize


    // blog
    Route::get('themes/{id}/manage-blog/{business}', [BlogController::class, 'blogManage'])->name('blog.manage');
    Route::get('themes/{id}/blog/{business}', [BlogController::class, 'blogCreate'])->name('blog.create');
    Route::resource('blogs', BlogController::class);
    // End blog

    // testimonial
    Route::get('themes/{id}/manage-testimonial/{business}', [TestimonialController::class, 'testimonialManage'])->name('testimonial.manage');
    Route::get('themes/{id}/testimonial/{business}', [TestimonialController::class, 'testimonialCreate'])->name('testimonial.create');
    Route::resource('testimonials', TestimonialController::class);
    // End testimonial

    // Plans
    Route::resource('plans', PlanController::class);

    Route::get('plan/list', [PlanController::class, 'PlanList'])->name('plan.list');
    Route::post('plan/store', [PlanController::class, 'PlanStore'])->name('plan.store');

    Route::get('plan/active', [PlanController::class, 'ActivePlans'])->name('active.plans');
    Route::any('plan/package-data', [PlanController::class, 'PackageData'])->name('package.data');
    Route::get('plan/plan-buy/{id}', [PlanController::class, 'PlanBuy'])->name('plan.buy');
    Route::get('plan/plan-trial/{id}', [PlanController::class, 'PlanTrial'])->name('plan.trial');
    Route::get('plan/order', [PlanController::class, 'orders'])->name('plan.order.index');
    Route::get('add-one/detail/{id}', [PlanController::class, 'AddOneDetail'])->name('add-one.detail');
    Route::post('add-one/detail/save/{id}', [PlanController::class, 'AddOneDetailSave'])->name('add-one.detail.save');

    Route::get('plan/order-refund/{id}', [PlanController::class, 'planRefund'])->name('order.refund');
    Route::post('plan-enable', [PlanController::class, 'planEnable'])->name('plan.enable');


    Route::post('company/settings-save', [CompanySettingsController::class, 'store'])->name('company.settings.save');
    Route::post('super-admin/settings-save', [SuperAdminSettingsController::class, 'store'])->name('super.admin.settings.save');
    Route::post('storage-settings-save', [SuperAdminSettingsController::class, 'storageStore'])->name('storage.setting.store');

    Route::post('super-admin/custom-js-save', [SuperAdminSettingsController::class, 'customJsStore'])->name('super.admin.custom.js.save');
    Route::post('super-admin/custom-css-save', [SuperAdminSettingsController::class, 'customCssStore'])->name('super.admin.custom.css.save');

    Route::post('cookie-settings-save', [SuperAdminSettingsController::class, 'CookieSetting'])->name('cookie.setting.store');


    // Coupon
    Route::resource('coupons', CouponController::class);
    Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon');
    // end Coupon

    // Module Install
    Route::get('modules/list', [ModuleController::class, 'index'])->name('module.index');
    Route::get('modules/add', [ModuleController::class, 'add'])->name('module.add');
    Route::post('install-modules', [ModuleController::class, 'install'])->name('module.install');
    Route::post('remove-modules/{module}', [ModuleController::class, 'remove'])->name('module.remove');
    Route::post('modules-enable', [ModuleController::class, 'enable'])->name('module.enable');
    Route::get('cancel/add-on/{name}', [ModuleController::class, 'CancelAddOn'])->name('cancel.add.on');
    // End Module Install

    // Email Templates
    Route::resource('email-templates', EmailTemplateController::class);
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'show'])->name('manage.email.language');
    Route::put('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name('store.email.language');
    // Route::put('email_template_status/{id}', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language');
    Route::resource('email_template', EmailTemplateController::class);
    // End Email Templates

    //notification
    Route::resource('notification-template', NotificationController::class);
    Route::get('notification-template/{id}/{lang?}', [NotificationController::class, 'show'])->name('manage.notification.language');
    Route::post('notification-template/{pid}', [NotificationController::class, 'storeNotificationLang'])->name('store.notification.language');

    // Routes For OnlineAppointment Option.
    Route::get('online-appointment-create/{serviceId}/{businessId}', [ServiceController::class, 'createOnlineAppointment'])->name('create.online.appointment');
    Route::post('save-online-meeting-setting/{serviceId}', [ServiceController::class, 'saveOnlineMeetingSetting'])->name('save.online.meeting.setting');


});

Route::middleware(['web'])->group(function (){
    Route::get('find-appointment/{businessSlug}', [HomeController::class, 'findAppointment'])->name('find.appointment');
    Route::post('track-appointment/{businessSlug}', [HomeController::class, 'trackAppointment'])->name('track.appointment');
});

Route::get('module/reset', [ModuleController::class, 'ModuleReset'])->name('module.reset');
Route::post('guest/module/selection', [ModuleController::class, 'GuestModuleSelection'])->name('guest.module.selection');

// cookie
Route::get('cookie/consent', [SuperAdminSettingsController::class, 'CookieConsent'])->name('cookie.consent');

// cache
Route::get('/config-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return redirect()->back()->with('success', 'Cache Clear Successfully');
})->name('config.cache');

Route::get('composer/json',function(){
    $path = base_path('packages/workdo');
    $modules = \Illuminate\Support\Facades\File::directories($path);

    $moduleNames = array_map(function($dir) {
        return basename($dir);
    }, $modules);

    $require = '';
    $repo = '';
    foreach($moduleNames as $module){
        $packageName = preg_replace('/([a-z])([A-Z])/', '$1-$2', $module);
        $require .= '"workdo/'.strtolower($packageName).'": "dev-testing",';
        $repo .= '{
            "type": "path",
            "url": "packages/workdo/'.$module.'"
        },';
    }
    return $require . '<br><br><br>' . $repo;
});

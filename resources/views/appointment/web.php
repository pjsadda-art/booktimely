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
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CustomerController;
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
Route::middleware(['auth', 'verified'])->group(function () {

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

    // service
    Route::resource('service', ServiceController::class);
    // End service

    // service
    Route::resource('staff', StaffController::class);
    // End service

    // appointment
    Route::resource('appointment', AppointmentController::class);

    Route::post('appointment/list', [AppointmentController::class, 'index'])->name('appointment.list.index');

    Route::get('appointment-calendar', [AppointmentController::class, 'appointmentCalendar'])->name('appointment.calendar');
    Route::get('appointment-calendar-create', [AppointmentController::class, 'calendarCreate'])->name('appointment.calendar.create');
    Route::post('appointment-calendar-store', [AppointmentController::class, 'calendarStore'])->name('appointment.calendar.store');
    Route::get('appointment-calendar-events', [AppointmentController::class, 'calendarEventsJson'])->name('appointment.calendar.events');
    Route::get('appointment-details/{id}', [AppointmentController::class, 'appointmentDetails'])->name('appointment.details');


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
    Route::resource('customer', CustomerController::class);
    Route::get('customer-list', [CustomerController::class, 'customerList'])->name('customer.list');
    Route::any('customer-ajax-create', [CustomerController::class, 'customerAjaxCreate'])->name('customer.ajax.create');
    Route::post('customer-ajax-submit', [CustomerController::class, 'customerAjaxSubmit'])->name('customer.ajax.submit');
    Route::get('/api/customers/search', [CustomerController::class, 'search'])->name('customers.search');


    // End customer

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

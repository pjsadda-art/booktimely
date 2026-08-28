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
use Workdo\Reports\Http\Controllers\AllAppointmentReportController;
use Workdo\Reports\Http\Controllers\AnalyticsController;
use Workdo\Reports\Http\Controllers\AppointmentStatusReportController;
use Workdo\Reports\Http\Controllers\CustomerVsGuestReportController;
use Workdo\Reports\Http\Controllers\RevenueReportController;
use Workdo\Reports\Http\Controllers\ServiceAppointmentReportController;

Route::middleware(['web', 'auth', 'PlanModuleCheck:Reports'])->group(function () {
    Route::prefix('reports')->group(function () {
        Route::resource('analytics', AnalyticsController::class);
        Route::any('analytics-index', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::resource('status-report', AppointmentStatusReportController::class);
        Route::resource('customer-guest-report', CustomerVsGuestReportController::class);
        Route::resource('appointment-report', AllAppointmentReportController::class);
        Route::resource('service-appointment-report', ServiceAppointmentReportController::class);
        Route::resource('revenue-report', RevenueReportController::class);
        Route::get('appointment-status-report', [AppointmentStatusReportController::class, 'fetchStatusReportData'])->name('appointment.status.report');
        Route::get('appointment-data', [AllAppointmentReportController::class, 'fetchAppointmentReportData'])->name('appointment.data');
        Route::get('appointment-customer-guest-report', [CustomerVsGuestReportController::class, 'fetchCustomerReportData'])->name('appointment.customer.guest.report');
        Route::get('service-report-data', [ServiceAppointmentReportController::class, 'fetchServiceReportData'])->name('service.appointment.report');
        Route::get('revenue-appointment-report', [RevenueReportController::class, 'fetchRevenueReportData'])->name('reveneue.appointment.report');
        Route::get('location-revenue-report', [RevenueReportController::class, 'revenueByLocation'])->name('location.revenue.report');
        Route::get('staff-revenue-report', [RevenueReportController::class, 'revenueByStaff'])->name('staff.revenue.report');
        Route::get('location-appointment-report', [AllAppointmentReportController::class, 'appointmentsByLocation'])->name('location.appointment.report');
        Route::get('staff-appointment-report', [AllAppointmentReportController::class, 'appointmentsByStaff'])->name('staff.appointment.report');
    });
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\ColorSchemeController;
use App\Http\Controllers\dashboard\DashboardController;
use App\Http\Controllers\program\ProgramController;
use App\Http\Controllers\organization\OrganizationController;
use App\Http\Controllers\semester\SemesterController;
use App\Http\Controllers\events\EventsController;
use App\Http\Controllers\college\CollegeController;
use App\Http\Controllers\students\StudentsController;
use App\Http\Controllers\scanner\ScannerController;
use App\Http\Controllers\attendance\AttendanceController;
use App\Http\Controllers\myattendance\MyAttendanceController;
use App\Http\Controllers\systemsettings\SystemSettingsController;
use App\Http\Controllers\listpaymentrequest\ListPaymentRequestController;
use App\Http\Controllers\profile\ProfileController;
use App\Http\Controllers\calendar\CalendarController;
use App\Http\Controllers\chat\ChatController;
use App\Http\Controllers\announcement\AnnouncementController;
use App\Http\Controllers\permission\PermissionController;
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


Route::controller(AuthController::class)->middleware('loggedin')->group(function() {
    Route::get('login', 'loginView')->name('login.index');
    Route::post('login', 'login')->name('login.check');
});

Route::middleware('auth:web,students')->group(function() {
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard.dashboard');
    
    Route::controller(ProgramController::class)->group(function() {
        Route::get('program/add', 'addProgram')->name('program.add-program');
        Route::post('program/store', 'store')->name('program.store');
        Route::get('program/edit/{id}', 'edit')->name('program.edit');
        Route::put('program/update/{id}', 'update')->name('program.update');
        Route::delete('program/delete/{id}', 'destroy')->name('program.destroy');
    });

    Route::controller(OrganizationController::class)->group(function() {
        Route::get('organization/add', 'addOrganization')->name('organization.add-organization');
        Route::post('organization/store', 'store')->name('organization.store');
        Route::get('organization/edit/{id}', 'edit')->name('organization.edit');
        Route::put('organization/update/{id}', 'update')->name('organization.update');
        Route::delete('organization/delete/{id}', 'destroy')->name('organization.destroy');
    });

    Route::controller(SemesterController::class)->group(function() {
        Route::get('semester/add', 'addSemester')->name('semester.add-semester');
        Route::post('semester/store', 'store')->name('semester.store');
        Route::get('semester/edit/{id}', 'edit')->name('semester.edit');
        Route::put('semester/update/{id}', 'update')->name('semester.update');
        Route::delete('semester/delete/{id}', 'destroy')->name('semester.destroy');
    });

    Route::controller(EventsController::class)->group(function() {
        Route::get('events/add', 'addEvent')->name('events.add-event');
        Route::post('events/store', 'store')->name('events.store');
        Route::get('events/edit/{id}', 'edit')->name('events.edit');
        Route::get('events/details/{id}', 'getEventDetails')->name('events.details');
        Route::put('events/update/{id}', 'update')->name('events.update');
        Route::delete('events/delete/{id}', 'destroy')->name('events.destroy');
        // Participants
        Route::get('events/participants/{id}', 'getParticipantsFormData')->name('events.participants.form');
        Route::post('events/participants/save', 'saveParticipants')->name('events.participants.save');
    });

    Route::controller(CollegeController::class)->group(function() {
        Route::get('college/add', 'addCollege')->name('college.add-college');
        Route::post('college/store', 'store')->name('college.store');
        Route::get('college/edit/{id}', 'edit')->name('college.edit');
        Route::put('college/update/{id}', 'update')->name('college.update');
        Route::delete('college/delete/{id}', 'destroy')->name('college.destroy');
    });

    Route::controller(StudentsController::class)->group(function() {
        Route::get('students/add', 'addStudents')->name('students.add-students');
        Route::post('students/store', 'store')->name('students.store');
        Route::get('students/edit/{id}', 'edit')->name('students.edit');
        Route::put('students/update/{id}', 'update')->name('students.update');
        Route::delete('students/delete/{id}', 'destroy')->name('students.destroy');
        Route::post('students/update-barcode/{id}', 'updateBarcode')->name('students.update-barcode');
    });

    Route::controller(ScannerController::class)->group(function() {
        Route::get('scanner', 'scanner')->name('scanner.scanner');
        Route::post('scanner/search', 'search')->name('scanner.search');
        Route::get('scanner/details/{id}', 'details')->name('scanner.details');
    });

    Route::controller(AttendanceController::class)->group(function() {
        Route::get('attendance', 'attendance')->name('attendance.attendance');
        Route::get('attendance/list', 'getAttendance')->name('attendance.list');
        Route::get('attendance/student', 'getStudentAttendance')->name('attendance.student');
    });

    Route::controller(MyAttendanceController::class)->group(function() {
        Route::get('myattendance', 'myAttendance')->name('myattendance.myAttendance');
        Route::get('myattendance/list', 'getAttendanceList')->name('myattendance.list');
        Route::get('myattendance/event/{eventId}/details', 'getEventAttendanceDetails')->name('myattendance.event.details');
        Route::post('myattendance/cart/save', 'saveCartItems')->name('myattendance.cart.save');
        Route::get('myattendance/receipt/{paymentId}', 'getReceiptDetails')->name('myattendance.receipt');
    });

    Route::controller(SystemSettingsController::class)->group(function() {
        Route::get('systemsettings/add', 'addSystemSettings')->name('systemsettings.add-systemsettings');
        Route::get('systemsettings/get/{id}', 'get')->name('systemsettings.get');
        Route::post('systemsettings/update', 'update')->name('systemsettings.update');
    });

    Route::controller(ListPaymentRequestController::class)->group(function() {
        Route::get('listpaymentrequest', 'listPaymentRequest')->name('listpaymentrequest.list');
        Route::get('listpaymentrequest/{id}/details', 'getPaymentDetails')->name('listpaymentrequest.details');
        Route::post('listpaymentrequest/{id}/approve', 'approvePayment')->name('listpaymentrequest.approve');
        Route::post('listpaymentrequest/{id}/decline', 'declinePayment')->name('listpaymentrequest.decline');
        Route::post('listpaymentrequest/{id}/waiver', 'addWaiver')->name('listpaymentrequest.waiver');
        Route::post('listpaymentrequest/{id}/generate-receipt', 'generateReceipt')->name('listpaymentrequest.generate-receipt');
    });

    Route::controller(ProfileController::class)->group(function() {
        Route::get('profile', 'index')->name('profile.index');
        Route::post('profile/update', 'updateProfile')->name('profile.update');
        Route::post('profile/change-password', 'changePassword')->name('profile.change-password');
        Route::post('profile/update-photo', 'updatePhoto')->name('profile.update-photo');
    });

    Route::controller(CalendarController::class)->group(function() {
        Route::get('calendar', 'calendar')->name('calendar.calendar');
    });

    Route::controller(ChatController::class)->group(function() {
        Route::get('chat', 'chat')->name('chat.chat');
        Route::post('chat/send', 'sendMessage')->name('chat.send');
        Route::get('chat/messages/{userId}', 'getMessages')->name('chat.messages');
        Route::get('chat/conversations', 'getConversationsList')->name('chat.conversations');
    });

    Route::controller(AnnouncementController::class)->group(function() {
        Route::get('announcement', 'announcement')->name('announcement.announcement');
        Route::post('announcement/store', 'store')->name('announcement.store');
        Route::get('announcement/edit/{id}', 'edit')->name('announcement.edit');
        Route::put('announcement/update/{id}', 'update')->name('announcement.update');
        Route::delete('announcement/delete/{id}', 'destroy')->name('announcement.destroy');
    });

    Route::controller(PermissionController::class)->group(function() {
        Route::get('permission', 'permission')->name('permission.permission');
        Route::post('permission/store', 'store')->name('permission.store');
        Route::get('permission/edit/{id}', 'edit')->name('permission.edit');
        Route::put('permission/update/{id}', 'update')->name('permission.update');
        Route::delete('permission/delete/{id}', 'destroy')->name('permission.destroy');
    });
});

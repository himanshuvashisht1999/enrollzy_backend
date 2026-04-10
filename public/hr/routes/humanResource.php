<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hr\RoleController;
use App\Http\Controllers\Hr\ClockController;
use App\Http\Controllers\Hr\StaffController;
use App\Http\Controllers\Hr\LeavesController;
use App\Http\Controllers\Hr\PayoutController;
use App\Http\Controllers\Hr\HolidayController;
use App\Http\Controllers\Hr\PayRollController;
use App\Http\Controllers\Hr\AdvancePayController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\DesignationController;
use App\Http\Controllers\Hr\LeaveSettingController;
use App\Http\Controllers\Hr\LeavePolicyController;
use App\Http\Controllers\Hr\BanksController;



Route::get('/get-attendence', [AttendanceController::class, 'getAttandence']);

Route::resource('department', DepartmentController::class);

Route::resource('designation', DesignationController::class);
Route::resource('banks', BanksController::class);

Route::resource('roles', RoleController::class);

Route::group(['prefix' => 'roless', 'as' => 'roless.'], function () { //  staff routes
    Route::controller(RoleController::class)->group(function () {
        Route::get('/get-designations/{department_id}', 'getDesignations')->name('getDesignations');
        Route::get('/get-users/{designation_id}', 'getUsers')->name('getUsers');

    });
});

Route::resource('payOut', PayoutController::class);

Route::resource('advance', AdvancePayController::class);

Route::resource('holidays', HolidayController::class);

Route::resource('leaveSetting', LeaveSettingController::class);
Route::resource('leavePolicy', LeavePolicyController::class);

Route::resource('leaves', LeavesController::class);

Route::resource('staff', StaffController::class);


Route::post('/get-advance-pay-amount', [AdvancePayController::class, 'getAdvancePayAmount'])->name('get.advance.pay.amount');
Route::post('/advance/storeBonus', [AdvancePayController::class, 'storeBonus'])->name('advance.storeBonus');


Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () { //  staff routes
    Route::controller(StaffController::class)->group(function () {
        Route::post('role_update', 'changeStaffRole')->name('role_update');
        Route::post('validate_username', 'validateUsername')->name('validate_username');
        Route::post('emailSend', 'emailSendToStaff')->name('emailSend');
        Route::post('emailVerify', 'verifyStaffEmail')->name('emailVerify');
        Route::post('mobileSend', 'otpSendToStaff')->name('mobileSend');
        Route::post('mobileVerify', 'otpVerifyToStaff')->name('mobileVerify');
        Route::post('update_document/{id}', 'updateDocuments')->name('update_document');
        Route::delete('destroy_doc/{url}', 'deleteStaffDocument')->name('destroy_doc');
    });
});

Route::group(['prefix' => 'attendance', 'as' => 'attendance.'], function () { //  attendance routes
    Route::controller(AttendanceController::class)->group(function () {
        Route::get('index', 'showAttendance')->name('index');
        Route::post('get_details', 'getAtDetailsForDay')->name('get_details');
    });
});

Route::group(['prefix' => 'payroll', 'as' => 'payroll.'], function () { //  attendance routes
    Route::controller(PayRollController::class)->group(function () {
        Route::get('index', 'showPayroll')->name('index');
        Route::post('calculate_salary', 'calculateMonthWiseSalary')->name('calculate_salary');
        Route::post('makeEmployeePayment', 'makeEmployeePayment')->name('makeEmployeePayment');
    });
});

Route::controller(ClockController::class)->group(function () {
    Route::group(['prefix' => 'clock', 'as' => 'clock.'], function () {
        Route::post('check_in', 'checkInAttendance')->name('check_in');
        Route::post('start_break', 'startBreakTime')->name('start_break');
        Route::post('end_lunchBreak', 'endLunchBreak')->name('end_lunchBreak');
        Route::post('check_out', 'checkOutAttendance')->name('check_out');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;




Route::resource('organization', OrganizationController::class);


// Route::group(['prefix' => 'payroll', 'as' => 'payroll.'], function () { //  attendance routes
//     Route::controller(PayRollController::class)->group(function () {
//         Route::get('index', 'showPayroll')->name('index');
//         Route::post('calculate_salary', 'calculateMonthWiseSalary')->name('calculate_salary');
//         Route::post('makeEmployeePayment', 'makeEmployeePayment')->name('makeEmployeePayment');
//     });
// });


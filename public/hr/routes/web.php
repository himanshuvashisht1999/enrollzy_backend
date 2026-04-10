<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\Backend\General\DashboardController;

Route::controller(AdminLoginController::class)->group(function () {
    Route::get('/', 'showLoginForm')->name('login');
    Route::post('checkCredentials', 'tryLoginUsingCredentials')->name('checkCredentials');
    Route::post('checkAccount', 'checkAccountForForgotPassword')->name('checkAccount');
    Route::get('logout', 'logout')->name('logout');
    Route::view('passwordReset', 'auth.forgot')->name('passwordReset');
    Route::get('check_password_token/{token}', 'checkAccountForForgotPassword')->name('check_password_token');
});

include 'fileManager.php'; // file manager route

Route::group(['middleware' => ['auth:admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'showDashboard')->name('dashboard');
        Route::get('fetchPinCode', 'fetchPinCode')->name('fetchPinCode');
        Route::get('readProductLogs', 'getProductQuantityLog')->name('readProductLogs');
        Route::get('clear-cache', 'clearCacheAdmin')->name('clear-cache');
    });
    include 'general.php'; // all general routes
    include 'humanResource.php'; // all HR module routes
    include 'organizations.php'; // all HR module routes
    include 'project.php'; // all Project module routes
});
// public route for Short URL only
Route::get('sturl/{url}', 'App\Http\Controllers\Backend\Seo\ShortenUrlController@checkOrRedirectToUrl')->name('sturl');
// public route for Short URL only

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\General\MenuController;
use App\Http\Controllers\Backend\General\PagesController;
use App\Http\Controllers\Backend\General\CouponController;
use App\Http\Controllers\Backend\General\MarqueeController;
use App\Http\Controllers\Backend\General\ProfileController;
use App\Http\Controllers\Backend\General\SettingController;
use App\Http\Controllers\Backend\General\MenuItemController;
use App\Http\Controllers\Backend\General\CustomersController;
use App\Http\Controllers\Backend\General\ExportCsvController;
use App\Http\Controllers\Backend\General\FlashBannerController;
use App\Http\Controllers\Backend\General\SlideBannerController;

use App\Http\Controllers\Backend\General\WhatsappTemplateController;

Route::resource('whatsapp_template', WhatsappTemplateController::class);
Route::controller(WhatsappTemplateController::class)->group(function () { //  Setting routes
    Route::get('whatsapp_template/send-message/{id}', 'sendMessage')->name('whatsapp_template.sendMessage');
    Route::post('whatsapp_template/post-send-message', 'postSendMessage')->name('whatsapp_template.postSendMessage');
    Route::get('whatsapp_report', 'report')->name('whatsapp_template.report');
    Route::get('whatsapp_stop', 'whatsappStop')->name('whatsapp_template.whatsappStop');
    Route::post('getCategoryNumbers', 'getCategoryNumbers')->name('whatsapp_template.getCategoryNumbers');
});


Route::controller(CustomersController::class)->group(function () { //  Customers routes
    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::get('list', 'CustomerList')->name('list');
        Route::get('view/{id}', 'CustomerView')->name('view');
        Route::get('create', 'createCustomers')->name('create');
        Route::post('store', 'storeCustomers')->name('store');
        Route::get('edit/{id}', 'editCustomers')->name('edit');
        Route::PATCH('update/{id}', 'updateCustomers')->name('update');
        Route::post('update_profile', 'updateCustomerProfile')->name('update_profile');
        Route::post('update_password', 'updateCustomerPassword')->name('update_password');
        Route::post('sendVerificationEmail', 'sendMailForEmailVerification')->name('sendVerificationEmail');
        Route::post('verifyOTPforEMail', 'verifyOtpForEmailVerification')->name('verifyOTPforEMail');
        Route::post('sendVerificationmobile', 'sendOTPForMobileVerification')->name('sendVerificationmobile');
        Route::post('verifyOTPforMobile', 'verifyOtpForMobileVerification')->name('verifyOTPforMobile');
        // route for finding user
        Route::get('find_customer', 'findCustomerForBooking')->name('find_customer');
        Route::post('category-children', 'getChildren')->name('getChildren');
        Route::post('import-customer', 'importCustomer')->name('importCustomer');
        Route::get('download-customer-sample', 'downloadCustomerSample')->name('downloadCustomerSample');
        Route::post('import-customer', 'importCustomer')->name('importCustomer');
         
    });
    Route::group(['prefix' => 'customer_category', 'as' => 'customer_category.'], function () {
        Route::get('list', 'listCustomerCategory')->name('list');
        Route::post('save', 'saveCustomerCategory')->name('save');
        Route::post('getCat', 'getCustomerCategory')->name('getCat');
        Route::post('update', 'updateCustomerCategory')->name('update');
        Route::delete('delete/{id}', 'deleteCustomerCategory')->name('delete');
    });
    Route::group(['prefix' => 'institute', 'as' => 'institute.'], function () {
        Route::get('list', 'listInstitutes')->name('list');
        Route::post('save', 'saveInstitutes')->name('save');
        Route::post('getIns', 'getInstitutes')->name('getIns');
        Route::post('update', 'updateInstitutes')->name('update');
        Route::delete('delete/{id}', 'deleteInstitutes')->name('delete');
    });

    Route::group(['prefix' => 'customer-field', 'as' => 'customer_field.'], function () {
        Route::get('list', 'CustomerFieldList')->name('list');
        Route::get('create', 'createCustomerField')->name('create');
        Route::post('store', 'storeCustomerField')->name('store');
        Route::get('edit/{id}', 'editCustomerField')->name('edit');
        Route::PATCH('update/{id}', 'updateCustomerField')->name('update');
        Route::delete('delete/{id}', 'deleteCustomerField')->name('delete');

        
    });
});

Route::controller(ProfileController::class)->group(function () { //  Profile routes
    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::get('/', 'showProfile')->name('index');
        Route::post('update_profile', 'updateProfile')->name('update_profile');
        Route::post('update_password', 'updatePassword')->name('update_password');
        Route::post('delete_attendance_image', 'deleteAttendanceImage')->name('deleteAttendanceImage');
        Route::post('upload-attendance-image', 'uploadAttendanceImage')->name('uploadAttendanceImage');
    });
});

Route::controller(ExportCsvController::class)->group(function () { //  ExportCsv routes
    Route::group(['prefix' => 'export', 'as' => 'export.'], function () {
        Route::get('product', 'ExportProduct')->name('product');
    });
});

Route::resource('setting', SettingController::class);

Route::controller(SettingController::class)->group(function () { //  Setting routes

    Route::post('whatsapp-update', 'updatewhatsappcon')->name('setting.whatsappupdate');
    Route::group(['prefix' => 'social', 'as' => 'social.'], function () {
        Route::get('social_links', 'SocialLinks')->name('social_links');
    });
});


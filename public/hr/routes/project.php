<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Project\TaskController;
use App\Http\Controllers\Project\ClientController;
use App\Http\Controllers\Project\CallingController;
use App\Http\Controllers\Project\WhatsappController;
use App\Http\Controllers\Project\ProjectsController;
use App\Http\Controllers\Project\MilestoneController;
use App\Http\Controllers\Project\CallStatusController;
use App\Http\Controllers\Project\CallActionController;
use App\Http\Controllers\Project\LeadSourceController;
use App\Http\Controllers\Project\TaskCommentController;
use App\Http\Controllers\Project\ProjectCategoryController;


Route::resource('leadSource', LeadSourceController::class);

Route::resource('client', ClientController::class);

Route::resource('projectCategory', ProjectCategoryController::class);

Route::resource('projects', ProjectsController::class);

Route::resource('milestones', MilestoneController::class);

Route::resource('task', TaskController::class);
Route::controller(TaskCommentController::class)->group(function () { // product routes
    Route::group(['prefix' => 'taskcomment', 'as' => 'taskcomment.'], function () {
        Route::post('store/{task}', 'store')->name('store');
    });
});

Route::post('task/get_staff_milestone', [TaskController::class, 'getProjectStaffMilestone'])->name('task.get_staff_milestone');
Route::post('task/update_document/{id}', [TaskController::class, 'updateDocuments'])->name('task.update_document');
Route::delete('task/destroy_doc/{url}', [TaskController::class, 'deleteStaffDocument'])->name('task.destroy_doc');

// Route::delete('destroy_doc/{url}', 'deleteStaffDocument')->name('destroy_doc');

// Route::resource('calling', CallingController::class);


Route::controller(CallingController::class)->group(function () { // product routes
    Route::group(['prefix' => 'calling', 'as' => 'calling.'], function () {
        Route::get('index', 'index')->name('index');
        Route::get('history', 'history')->name('history');
        Route::post('history-update-status/{id}', 'updateStatus');
        Route::post('create', 'create')->name('create');
        Route::post('upload-csv', 'store')->name('csv.store');
        Route::post('whatsapp_message_send', 'whatsapp_message_send')->name('whatsapp_message_send');
        Route::get('restart', 'restart')->name('restart');
        Route::post('calling_history_update', 'calling_history_update')->name('calling_history_update');
        Route::post('calling_history_upload_excel', 'calling_history_upload_excel')->name('calling_history_upload_excel');


    });
});
Route::controller(WhatsappController::class)->group(function () { // product routes
    Route::group(['prefix' => 'whatsapp', 'as' => 'whatsapp.'], function () {
        Route::get('index', 'index')->name('index');
        Route::post('send-message', 'sendMessage')->name('send');
    });
});

Route::resource('call_status', CallStatusController::class);
Route::resource('call_action', CallActionController::class);

<?php

use App\Http\Controllers\Api\V1\CounsellingApiController;
use App\Http\Controllers\Api\V1\ExamApiController;
use App\Http\Controllers\Api\V1\ExamBlockApiController;
use App\Http\Controllers\Api\V1\FollowApiController;
use App\Http\Controllers\Api\V1\OrganisationBlockApiController;
use Illuminate\Support\Facades\Route;

/*
| Public, read-only API for the student website (documented in docs/counselling-public-api.md).
| Everything here returns only PUBLISHED data. No login: students are identified by a confirmed
| email / phone only for "follow" and alerts.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    // ---- Reads: cacheable ----
    Route::middleware(['throttle:cns-public', 'cache.headers:public;max_age=60;etag'])->group(function () {
        Route::get('masters', [CounsellingApiController::class, 'masters'])->name('masters');
        Route::get('counselling', [CounsellingApiController::class, 'index'])->name('counselling.index');
        Route::get('counselling/{slug}', [CounsellingApiController::class, 'show'])->name('counselling.show');
        Route::get('counselling/{slug}/institutes', [CounsellingApiController::class, 'institutes'])->name('counselling.institutes');
        Route::get('counselling/{slug}/cutoffs', [CounsellingApiController::class, 'cutoffs'])->name('counselling.cutoffs');
        Route::get('counselling/{slug}/seat-matrix', [CounsellingApiController::class, 'seatMatrix'])->name('counselling.seat-matrix');
        Route::get('exams/{slug}', [ExamApiController::class, 'show'])->name('exams.show');
        Route::get('exams/{slug}/counselling', [ExamBlockApiController::class, 'show'])->name('exams.counselling');
        Route::get('organisations/{organisation}/admission-through', [OrganisationBlockApiController::class, 'show'])->name('organisations.admission');
    });

    // ---- Follow and alerts: never cached ----
    Route::middleware('throttle:cns-follow')->post('follow', [FollowApiController::class, 'follow'])->name('follow');
    Route::middleware('throttle:cns-public')->group(function () {
        Route::post('follow/verify-phone', [FollowApiController::class, 'verifyPhone'])->name('follow.verify-phone');
        Route::get('follow/confirm', [FollowApiController::class, 'confirmEmail'])->name('follow.confirm')->middleware('signed');
        Route::get('unsubscribe', [FollowApiController::class, 'unsubscribe'])->name('unsubscribe')->middleware('signed');

        // The inbox token is issued only after an email / phone has been confirmed.
        Route::get('inbox', [FollowApiController::class, 'inbox'])->name('inbox');
        Route::post('inbox/read', [FollowApiController::class, 'markRead'])->name('inbox.read');
        Route::get('follows', [FollowApiController::class, 'follows'])->name('follows');
        Route::post('follows/{subscription}/channels', [FollowApiController::class, 'updateChannels'])->whereNumber('subscription')->name('follows.channels');
        Route::delete('follows/{subscription}', [FollowApiController::class, 'unfollow'])->whereNumber('subscription')->name('follows.destroy');
    });
});

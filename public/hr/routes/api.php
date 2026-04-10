<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/optimize', function () {
    Artisan::call('config:cache');
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');
    return "Optimization cleared!";
});


Route::get('/permissions-setup', function () {
    Artisan::call('permissions:setup');
    return "Permission done linked!";
});

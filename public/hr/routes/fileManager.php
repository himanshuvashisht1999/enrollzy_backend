<?php

use UniSharp\LaravelFilemanager\Lfm;
use Illuminate\Support\Facades\Route;


Route::group(
    ['prefix' => 'filemanager', 'middleware' => ['auth:admin']],
    function () {
        Lfm::routes();
    }
);

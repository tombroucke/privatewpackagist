<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\PackagesJsonController;
use App\Http\Middleware\BasicAuth;
use Illuminate\Support\Facades\Route;

Route::prefix('repo')->middleware(BasicAuth::class)->group(function () {
    Route::get('packages.json', [PackagesJsonController::class, 'show']);
    Route::get('{file}', [FileController::class, 'download'])->where('file', '.*');
});

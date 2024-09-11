<?php

use App\Http\Controllers\PackagesJsonController;
use App\Http\Middleware\BasicAuth;
use Illuminate\Support\Facades\Route;

Route::middleware(BasicAuth::class)->group(function () {
    Route::get('/repo/packages.json', PackagesJsonController::class.'@show');

    Route::get('/repo/{file}', function ($file) {

        $allowedFileTypes = ['zip'];
        $fileType = pathinfo($file, PATHINFO_EXTENSION);
        $fileTypeAllowed = in_array($fileType, $allowedFileTypes);
        if (! $fileTypeAllowed) {
            abort(403);
        }

        $directoryTraversal = strpos($file, '..') !== false;
        if ($directoryTraversal) {
            abort(403);
        }

        $filePath = storage_path('app/packages/'.$file);
        if (file_exists($filePath)) {
            return response()->file($filePath);
        } else {
            abort(404);
        }
    })->where('file', '.*');
});

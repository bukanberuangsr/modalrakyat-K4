<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/upload/presigned', [UploadController::class, 'getPresignedUrl']);
    Route::get('/upload/validate', [UploadController::class, 'validateUploadFile']);
    Route::get('/admin/file/{filename}', [AdminController::class, 'getFile']);
});

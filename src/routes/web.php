<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'index']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::get('/upload/presigned', [UploadController::class, 'getPresignedUrl']);
    Route::get('/upload/validate', [UploadController::class, 'validateUploadFile']);
    Route::get('/admin/file/{filename}', [AdminController::class, 'getFile'])->middleware('role:admin');
});

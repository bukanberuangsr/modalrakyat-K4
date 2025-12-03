<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('auth:api')->group(function () {
    Route::get('/upload/presigned', [UploadController::class, 'getPresignedUrl']);
    Route::post('/upload/validate', [UploadController::class, 'validateUploadFile']);
    Route::get('/my/upload/', [UploadController::class, 'myUploads']);
});

Route::middleware(['auth:api', 'role:admin'])->group(function(){
    Route::get('/admin/file/{filename}', [AdminController::class, 'getFile']);
    Route::get('/admin/uploads', [AdminController::class, 'listUploads']);
    Route::get('/admin/uploads/{id}', [AdminController::class, 'viewUpload']);
    Route::get('/admin/uploads/{id}/verify', [AdminController::class, 'verifyUpload']);
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'registerView'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard/admin', function () {
    return view('dashboardAdmin');
})->name('dashboard');

Route::get('/dashboard/users', function () {
    return view('adminUsers');
})->name('admin.users');

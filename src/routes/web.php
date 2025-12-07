<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use App\Models\Upload;

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

// Dashboard Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])
        ->name('dashboard');
});

// Manajemen Users
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard/users', [AdminController::class, 'users'])
        ->name('admin.users');
});

// Update Role Users
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/user/{id}/role', [AdminController::class, 'updateRole'])
        ->name('user.updateRole');
});

// Home User
Route::get('/home', function () {
    // Sementara kosong dulu sampai tabel Upload & modelnya jadi
    $uploads = []; 
    return view('home', compact('uploads'));
})->name('home');

// Profile User
Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});

// Upload Dokumen
Route::post('/upload/document', [UploadController::class, 'upload'])
    ->name('upload.document');

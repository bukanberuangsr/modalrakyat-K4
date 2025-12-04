<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use App\Models\Upload;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/login');
});

Route::middleware('auth:api')->group(function () {
    Route::get('/upload/presigned', [UploadController::class, 'getPresignedUrl']);
    Route::get('/upload/validate', [UploadController::class, 'validateUploadFile']);
    Route::get('/admin/file/{filename}', [AdminController::class, 'getFile'])->middleware('role:admin');
});

// Login (GET)
Route::get('/login', [AuthController::class, 'index'])
    ->name('login');

// Login (POST)
Route::post('/login', [AuthController::class, 'login']);

// Register (GET)
Route::get('/register', [AuthController::class, 'registerView'])->name('register');

// Register (POST)
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::get('/dashboard/admin', function () {
    return view('dashboardAdmin');
})->name('dashboard');

Route::get('/dashboard/users', function () {
    return view('adminUsers');
})->name('admin.users');


Route::get('/home', function () {
    // Sementara kosong dulu sampai tabel Upload & modelnya jadi
    $uploads = []; 
    return view('home', compact('uploads'));
})->name('home');


// Upload Dokumen
Route::post('/upload/document', [UploadController::class, 'upload'])
    ->name('upload.document');

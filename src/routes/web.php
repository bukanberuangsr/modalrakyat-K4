<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use App\Models\Upload;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    'auth', 'throttle:30,1'
])->group(function () {
    Route::get('/whoami', function () {
        $user = auth('web')->user();
        return response()->json([
            'id' => auth('web')->id(),
            'email' => $user ? $user->email : null,
            'name' => $user ? $user->name : null
        ]);
    });
    Route::get('/upload/presigned', [UploadController::class, 'getPresignedUrl']);
    Route::post('/upload/validate', [UploadController::class, 'validateUploadFile']);
    Route::get('/my/upload/', [UploadController::class, 'myUploads']);
    
    // TAMBAHAN: Route untuk download file user
    Route::get('/user/file/{id}', [UploadController::class, 'downloadFile'])->name('user.download');
});

Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    'auth', 'role:admin', 'throttle:60,1'
])->group(function(){
    Route::get('/admin/file/{filename}', [AdminController::class, 'getFile']);
    Route::get('/admin/uploads', [AdminController::class, 'listUploads']);
    Route::get('/admin/uploads/{id}', [AdminController::class, 'showUploads']);
    Route::post('/admin/uploads/{id}/verify', [AdminController::class, 'verifyUpload'])->name('admin.verify.upload');
    Route::post('/admin/uploads/{id}/download-proxy', [AdminController::class, 'downloadProxy']);
    Route::get('/admin/uploads/{id}/meta', [AdminController::class, 'meta']);
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'registerView'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit')->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Admin
Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    'auth', 'role:admin', 'throttle:60,1'
])->group(function () {
    Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])
        ->name('dashboard');
});

// Manajemen Users
Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    'auth', 'role:admin', 'throttle:60,1'
])->group(function () {
    Route::get('/dashboard/users', [AdminController::class, 'users'])
        ->name('admin.users');
});

// Update Role Users
Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    'auth', 'role:admin', 'throttle:60,1'
])->group(function () {
    Route::post('/user/{id}/role', [AdminController::class, 'updateRole'])
        ->name('user.updateRole');
});

Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    'auth', 'throttle:30,1'
])->get('/home', function (Request $request) {
    // Ambil riwayat upload pengguna
    $user = auth('web')->user();
    $uploads = [];
    if ($user) {
        $uploads = Upload::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

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
    ->name('upload.document')->middleware('throttle:10,1');
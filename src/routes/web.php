<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use App\Models\Upload;

Route::get('/', function () {
    return redirect('/login');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'registerView'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit')->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// User Routes
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
    Route::post('/upload/document', [UploadController::class, 'upload'])->name('upload.document')->middleware('throttle:10,1');
    Route::get('/my/upload/', [UploadController::class, 'myUploads']);
    Route::get('/user/file/{id}', [UploadController::class, 'downloadFile'])->name('user.download');

    Route::get('/home', function () {
        $user = auth('web')->user();
        $uploads = [];
        if ($user) {
            $uploads = Upload::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return view('home', compact('uploads'));
    })->name('home');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});

// Admin Routes
Route::middleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    'auth', 'role:admin', 'throttle:60,1'
])->group(function () {
    // Dashboard & Users Management (HTML Views)
    Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/users', [AdminController::class, 'users'])->name('admin.users');

    // API Endpoints (Return JSON for AJAX)
    Route::get('/api/admin/stats', [AdminController::class, 'stats']);
    Route::get('/api/admin/uploads/{id}/detail', [AdminController::class, 'viewUploads']);
    Route::post('/api/admin/uploads/{id}/verify', [AdminController::class, 'verifyUpload']);
    Route::post('/user/{id}/role', [AdminController::class, 'updateRole'])->name('user.updateRole');

    // File Operations
    Route::get('/admin/uploads/{id}', [AdminController::class, 'detailUpload'])->name('admin.upload.detail'); // HTML view
    Route::get('/admin/uploads/{id}/download', [AdminController::class, 'downloadProxy'])->name('admin.upload.download'); // Download file
    Route::get('/admin/uploads/{id}/meta', [AdminController::class, 'meta']);
});

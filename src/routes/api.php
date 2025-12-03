<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AdminController;

Route::get('/admin/stats', [UserController::class, 'dashboardStats']);

Route::get('/stats', function () {
    return response()->json([
        'message' => 'API working',
        'timestamp' => now(),
    ]);
});
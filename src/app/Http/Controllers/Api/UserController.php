<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Upload;

class UserController extends Controller
{
    public function dashboardStats()
    {
        return response()->json([
            'total_users'      => User::count(),
            'pending_docs'     => Upload::where('status', 'pending')->count(),
            'rejected_docs'    => Upload::where('status', 'rejected')->count(),
            'encrypted_docs'   => Upload::count(),
        ]);
    }
}

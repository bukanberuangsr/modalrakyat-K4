<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function resetAdmin()
    {
        $user = User::where('email', 'admin@modalrakyat.com')->first();
        
        if ($user) {
            $newPassword = 'admin123';
            $user->password = Hash::make($newPassword);
            $user->save();
            
            return response()->json([
                'message' => 'Password reset successfully',
                'email' => $user->email,
                'new_password' => $newPassword,
                'new_hash' => $user->password,
            ]);
        }
        
        return response()->json(['error' => 'User not found'], 404);
    }
}

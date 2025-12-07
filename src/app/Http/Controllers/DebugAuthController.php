<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DebugAuthController extends Controller
{
    // Test endpoint untuk debug login
    public function testLogin(Request $request)
    {
        $email = 'admin@modalrakyat.com';
        $password = 'admin123';

        echo "=== DEBUG LOGIN ===\n";
        
        // 1. Cek user di database
        $user = User::where('email', $email)->first();
        echo "1. User found: " . ($user ? "YES (ID: {$user->id}, Role: {$user->role})" : "NO") . "\n";

        if ($user) {
            // 2. Cek password hash
            $passwordMatch = Hash::check($password, $user->password);
            echo "2. Password match: " . ($passwordMatch ? "YES" : "NO") . "\n";
            echo "   Stored hash: " . substr($user->password, 0, 20) . "...\n";
            echo "   Provided: {$password}\n";

            // 3. Coba attempt auth
            $attempt = auth('web')->attempt(['email' => $email, 'password' => $password]);
            echo "3. Auth attempt result: " . ($attempt ? "SUCCESS" : "FAILED") . "\n";

            // 4. Cek session
            echo "4. Session after attempt:\n";
            echo "   Auth user: " . (auth('web')->user() ? auth('web')->user()->email : "NULL") . "\n";
            echo "   Session ID: " . session()->getId() . "\n";
            echo "   Session data: " . json_encode(session()->all()) . "\n";
        }

        return response()->json([
            'user_exists' => $user ? true : false,
            'password_match' => $user && Hash::check($password, $user->password),
            'auth_user' => auth('web')->user(),
            'session_id' => session()->getId(),
        ]);
    }

    public function whoami(Request $request)
    {
        return response()->json([
            'cookies' => $request->cookies->all(),
            'session_id' => session()->getId(),
            'session_data' => session()->all(),
            'auth_user' => auth('web')->user(),
            'is_authenticated' => auth('web')->check(),
        ]);
    }
}

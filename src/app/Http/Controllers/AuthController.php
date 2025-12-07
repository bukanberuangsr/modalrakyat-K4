<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Index
    public function index()
    {
        return view('login');
    }

    public function registerView()
    {
        return view('register');
    }

    // Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        \Log::info('Login attempt', ['email' => $credentials['email']]);

        // Pertama coba gunakan guard 'api' (JWT) bila tersedia
        try {
            if ($token = @auth('api')->attempt($credentials)) {
                $user = auth('api')->user();
                return response()->json([
                    'token' => $token,
                    'type'  => 'bearer',
                    'user'  => $user,
                ]);
            }
        } catch (\Throwable $e) {
            // jika jwt guard belum terpasang/konfigurasi, lanjut ke session guard
        }

        // Fallback: gunakan session-based login (web guard) sehingga form web tetap berfungsi
        $attemptResult = auth('web')->attempt($credentials);
        \Log::info('Web guard attempt result', ['result' => $attemptResult ? 'success' : 'failed']);
        
        if ($attemptResult) {
            $user = auth('web')->user();
            \Log::info('Login success', ['user_id' => $user->id, 'role' => $user->role, 'authenticated' => auth('web')->check()]);

            // SELALU return JSON, jangan redirect di server
            // JavaScript di client akan handle redirect
            $redirectUrl = $user->role === 'admin' ? '/dashboard/admin' : '/home';
            \Log::info('Login will redirect to', ['url' => $redirectUrl]);
            
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'user'    => $user,
                'redirect_url' => $redirectUrl,
            ]);
        }

        \Log::info('Login failed - credentials invalid');
        return response()->json([
            'success' => false,
            'error' => 'Email atau password salah'
        ], 401);
    }

    // Register
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Create user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',  // default role
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
        ]);
    }

    // Logout
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Logged out']);
    }
}
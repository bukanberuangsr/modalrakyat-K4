<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DebugHeadersController extends Controller
{
    public function show(Request $request)
    {
        $cookies = [];
        foreach ($request->cookies->all() as $k => $v) {
            $cookies[$k] = $v;
        }

        $sessionId = $request->session()->getId();
        $sessionAll = $request->session()->all();

        $user = Auth::guard('web')->user();

        $payload = [
            'headers' => $this->filterHeaders($request->headers->all()),
            'cookies' => $cookies,
            'session_id' => $sessionId,
            'session_keys' => array_keys($sessionAll),
            'session_data_sample' => array_slice($sessionAll, 0, 20),
            'auth_user' => $user ? [
                'id' => $user->id ?? null,
                'email' => $user->email ?? null,
                'role' => $user->role ?? null,
            ] : null,
            'is_authenticated' => (bool) $user,
        ];

        Log::info('DebugHeaders requested', ['path' => $request->path(), 'auth' => (bool) $user]);

        return response()->json($payload);
    }

    protected function filterHeaders(array $all)
    {
        $out = [];
        foreach ($all as $k => $v) {
            $out[$k] = is_array($v) && count($v) === 1 ? $v[0] : $v;
        }
        return $out;
    }
}

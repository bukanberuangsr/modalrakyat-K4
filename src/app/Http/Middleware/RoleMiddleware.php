<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();
        
        \Log::info('RoleMiddleware check', [
            'path' => $request->path(),
            'user' => $user?->email,
            'user_role' => $user?->role,
            'required_role' => $role,
            'match' => $user && $user->role === $role,
        ]);

        if (!$user || $user->role !== $role) {
            \Log::warning('Role check failed', [
                'path' => $request->path(),
                'user' => $user?->email,
                'user_role' => $user?->role,
                'required_role' => $role,
            ]);
            
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            // Redirect to home for non-JSON requests
            return redirect('/');
        }

        return $next($request);
    }
}

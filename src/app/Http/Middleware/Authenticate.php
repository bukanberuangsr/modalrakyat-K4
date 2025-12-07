<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

class Authenticate
{
    public function __construct(protected AuthFactory $auth)
    {
    }

    public function handle(Request $request, Closure $next, ...$guards): mixed
    {
        $this->authenticate($request, $guards);
        
        \Log::info('Authenticate middleware passed', [
            'path' => $request->path(),
            'user' => auth('web')->user()?->email,
            'authenticated' => auth('web')->check(),
        ]);
        
        return $next($request);
    }

    protected function authenticate(Request $request, array $guards): void
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return;
            }
        }

        \Log::warning('User not authenticated, redirecting to login', [
            'path' => $request->path(),
            'guards' => $guards,
        ]);

        $this->unauthenticated($request, $guards);
    }

    protected function unauthenticated(Request $request, array $guards): void
    {
        throw new \Illuminate\Auth\AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectTo($request),
        );
    }

    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return route('login');
    }
}

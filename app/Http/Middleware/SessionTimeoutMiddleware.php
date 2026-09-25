<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $timeout = (int) config('session.inactivity_timeout', 30);
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity && now()->diffInSeconds($lastActivity, true) >= ($timeout * 60)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your session expired due to inactivity. Please sign in again.',
            ]);
        }

        $request->session()->put('last_activity_at', now());

        return $next($request);
    }
}
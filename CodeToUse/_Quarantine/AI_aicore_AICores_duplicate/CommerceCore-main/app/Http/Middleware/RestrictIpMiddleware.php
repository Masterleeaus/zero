<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictIpMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->allowed_ips) {
            $allowedIps = array_map('trim', explode(',', $user->allowed_ips));
            
            // Allow localhost for development
            if ($request->ip() === '127.0.0.1' || $request->ip() === '::1') {
                return $next($request);
            }

            if (!in_array($request->ip(), $allowedIps)) {
                auth()->logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Access denied: Your IP address (' . $request->ip() . ') is not authorized for this account.'
                ]);
            }
        }

        return $next($request);
    }
}

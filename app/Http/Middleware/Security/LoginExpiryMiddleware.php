<?php

declare(strict_types=1);

namespace App\Http\Middleware\Security;

use App\Models\Security\LoginExpiry;
use App\Models\Security\SecurityAuditEvent;
use App\Services\Security\SecurityAuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force logout when an authenticated user's expiry_date has passed.
 *
 * The expiry record is created via the security settings admin panel and
 * gives administrators the ability to force a user to re-authenticate at
 * a specific date.
 */
class LoginExpiryMiddleware
{
    public function __construct(private readonly SecurityAuditService $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $expiry = LoginExpiry::where('user_id', auth()->id())->first();

            if ($expiry && $expiry->expiry_date->isPast()) {
                $this->audit->record(
                    SecurityAuditEvent::TYPE_LOGIN_EXPIRY,
                    ['expiry_date' => $expiry->expiry_date->toDateString()],
                    $request->ip(),
                    auth()->user()?->email,
                    auth()->id(),
                );

                auth()->logout();

                if ($request->expectsJson()) {
                    return response()->json(['status' => 'fail', 'message' => 'Session expired. Please log in again.'], 401);
                }

                return redirect()->route('login')->with('message', 'Your session has expired. Please log in again.');
            }
        }

        return $next($request);
    }
}

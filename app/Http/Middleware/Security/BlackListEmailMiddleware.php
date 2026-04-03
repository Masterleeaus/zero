<?php

declare(strict_types=1);

namespace App\Http\Middleware\Security;

use App\Models\Security\BlacklistEmail;
use App\Models\Security\SecurityAuditEvent;
use App\Services\Security\SecurityAuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block requests whose email (or email domain) is on the blacklist.
 *
 * Checks both the exact address and the @domain prefix.
 * Fires a security audit event on block.
 */
class BlackListEmailMiddleware
{
    public function __construct(private readonly SecurityAuditService $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');

        if (! $email && auth()->check()) {
            $email = auth()->user()?->email;
        }

        if ($email && $this->isBlacklisted($email)) {
            $this->audit->record(
                SecurityAuditEvent::TYPE_EMAIL_BLOCKED,
                ['email' => $email, 'path' => $request->path()],
                $request->ip(),
                $email,
            );

            if ($request->expectsJson()) {
                return response()->json(['status' => 'fail', 'message' => 'Access denied: email is blocked.'], 403);
            }

            abort(403, 'Access denied: email is blocked.');
        }

        return $next($request);
    }

    private function isBlacklisted(string $email): bool
    {
        if (! str_contains($email, '@')) {
            return BlacklistEmail::where('email', $email)->exists();
        }

        $domain = '@' . str($email)->after('@')->toString();

        return BlacklistEmail::where('email', $email)
            ->orWhere('email', $domain)
            ->exists();
    }
}

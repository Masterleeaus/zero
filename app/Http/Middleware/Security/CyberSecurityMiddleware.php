<?php

declare(strict_types=1);

namespace App\Http\Middleware\Security;

use App\Events\Security\LoginLockoutEvent;
use App\Models\Security\BlacklistEmail;
use App\Models\Security\BlacklistIp;
use App\Models\Security\CyberSecurityConfig;
use App\Models\Security\SecurityAuditEvent;
use App\Models\User;
use App\Services\Security\SecurityAuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * CyberSecurityMiddleware
 *
 * Enforces login-attempt rate limiting, progressive lockout escalation,
 * lockout alerting, blacklist checks on registration / login, and
 * optional unique-session enforcement.
 *
 * Adapted from CyberSecurity module (CodeToUse/PWA/platform/CyberSecurity).
 * Integrated into Titan Zero host middleware stack.
 */
class CyberSecurityMiddleware
{
    public function __construct(private readonly SecurityAuditService $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        $config = CyberSecurityConfig::singleton();

        // ── Login / registration hardening ────────────────────────────────
        if ($this->isAuthRequest($request)) {
            $email = (string) $request->input('email', '');

            // Auto-blacklist IPs that register more than 2 accounts within 5 min
            if ($this->isMassRegistration($request)) {
                BlacklistIp::firstOrCreate(['ip_address' => $request->ip()]);

                $this->audit->record(
                    SecurityAuditEvent::TYPE_IP_BLOCKED,
                    ['reason' => 'mass_registration'],
                    $request->ip(),
                    $email,
                );

                return $this->denyJson('IP blocked due to suspicious registration activity.', $request);
            }

            // Email blacklist check
            if ($email && BlacklistEmail::where('email', $email)->exists()) {
                $this->audit->record(
                    SecurityAuditEvent::TYPE_EMAIL_BLOCKED,
                    ['context' => 'auth_attempt'],
                    $request->ip(),
                    $email,
                );

                return $this->denyLogin('Access denied: email is blocked.', $request);
            }

            // Hard lockout limit reached
            $lockoutKey = 'security:lockout:' . $request->ip();

            if (RateLimiter::attempts($lockoutKey) >= $config->max_lockouts) {
                $this->audit->record(
                    SecurityAuditEvent::TYPE_RATE_LIMIT,
                    ['lockout_count' => RateLimiter::attempts($lockoutKey)],
                    $request->ip(),
                    $email,
                );

                return $this->tooManyAttempts($lockoutKey, $request);
            }

            // Per-IP retry rate limit
            $retryKey = 'security:login:' . $request->ip();

            if (RateLimiter::tooManyAttempts($retryKey, $config->max_retries)) {
                return $this->tooManyAttempts($retryKey, $request);
            }

            RateLimiter::attempt(
                $retryKey,
                $config->max_retries,
                function () {},
                $this->getLockoutSeconds($config),
            );

            if (RateLimiter::tooManyAttempts($retryKey, $config->max_retries)) {
                $this->handleLockoutEscalation($config, $lockoutKey, $email, $request->ip());
            }
        }

        // ── Unique-session enforcement ─────────────────────────────────────
        if (auth()->check() && $config->unique_session) {
            $this->revokeOtherSessions();
        }

        return $next($request);
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function isAuthRequest(Request $request): bool
    {
        return $request->input('email')
            && $request->isMethod('post')
            && (
                str_contains((string) $request->url(), 'login')
                || str_contains((string) $request->url(), 'register')
                || $request->routeIs('accept_invite')
                || $request->routeIs('front.signup.store')
                || $request->routeIs('front.client-register')
            );
    }

    private function isMassRegistration(Request $request): bool
    {
        return User::where('register_ip', $request->ip())
            ->whereBetween('created_at', [now()->subMinutes(5), now()])
            ->count() > 1;
    }

    private function getLockoutSeconds(CyberSecurityConfig $config): int
    {
        $escalations = RateLimiter::attempts('security:lockout:' . request()->ip());
        $extra       = $escalations > 0 ? $config->extended_lockout_time * 3600 : 0;

        return (int) (($config->lockout_time * 60) + $extra);
    }

    private function handleLockoutEscalation(
        CyberSecurityConfig $config,
        string $lockoutKey,
        string $email,
        string $ip,
    ): void {
        RateLimiter::hit($lockoutKey, $config->reset_retries * 3600);

        $attempts = RateLimiter::attempts($lockoutKey);

        if ($config->alert_after_lockouts && $attempts === $config->alert_after_lockouts) {
            event(new LoginLockoutEvent($email, $ip));
        }

        $this->audit->record(
            SecurityAuditEvent::TYPE_LOGIN_LOCKOUT,
            ['lockout_count' => $attempts, 'email' => $email],
            $ip,
            $email,
        );
    }

    private function revokeOtherSessions(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', auth()->id())
            ->where('id', '!=', request()->session()->getId())
            ->delete();

        $this->audit->record(
            SecurityAuditEvent::TYPE_SESSION_REVOKED,
            ['reason' => 'unique_session_policy'],
            request()->ip(),
            auth()->user()?->email,
            auth()->id(),
        );
    }

    private function tooManyAttempts(string $key, Request $request): Response
    {
        $seconds = RateLimiter::availableIn($key);
        $message = 'Too many attempts. Please try again in ' . now()->addSeconds($seconds)->diffForHumans() . '.';

        if ($request->expectsJson()) {
            return response()->json(['status' => 'fail', 'message' => $message], 429);
        }

        return redirect()->route('login')->with('message', $message);
    }

    private function denyJson(string $message, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 'fail', 'message' => $message], 403);
        }

        return redirect()->route('login')->with('message', $message);
    }

    private function denyLogin(string $message, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 'fail', 'message' => $message], 403);
        }

        return redirect()->route('login')->with('message', $message);
    }
}

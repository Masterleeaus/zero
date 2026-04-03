<?php

namespace App\Http\Middleware\Titan;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Rate-limit TitanAIRouter execution path.
 *
 * Enforces tenant-level budgets and per-user request throttling
 * before provider execution occurs.
 *
 * Defaults:
 *  - 60 AI calls per minute per user
 *  - Tenant budget checked against titan_budgets config
 */
class TitanAIRateLimitMiddleware
{
    private const RATE_LIMIT_WINDOW_SECONDS = 60;
    private const DAILY_WINDOW_SECONDS      = 86400;

    public function __construct(protected RateLimiter $limiter)
    {
    }

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $user      = $request->user();
        $companyId = $user?->company_id ?? 0;
        $userId    = $user?->id ?? 0;

        // Per-user rate limit
        $userKey        = "titan-ai:user:{$userId}";
        $userMaxPerMin  = (int) config('titan_core.ai.rate_limit_per_user', 60);

        if ($this->limiter->tooManyAttempts($userKey, $userMaxPerMin)) {
            return response()->json([
                'error'       => 'Rate limit exceeded for your account.',
                'retry_after' => $this->limiter->availableIn($userKey),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($userKey, self::RATE_LIMIT_WINDOW_SECONDS);

        // Per-company budget gate
        $companyKey       = "titan-ai:company:{$companyId}:daily";
        $companyDailyMax  = (int) config('titan_budgets.per_company_daily', 0);

        if ($companyDailyMax > 0 && $this->limiter->tooManyAttempts($companyKey, $companyDailyMax)) {
            return response()->json([
                'error' => 'Company daily AI token budget exceeded.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($companyDailyMax > 0) {
            $this->limiter->hit($companyKey, self::DAILY_WINDOW_SECONDS);
        }

        return $next($request);
    }
}

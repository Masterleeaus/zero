<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Rate limits Omni conversation endpoints to prevent abuse.
 * Uses Laravel's rate limiter with configurable per-channel thresholds.
 *
 * Configuration (config/omni.php):
 *   'rate_limits' => [
 *       'conversation.store' => '100,1', // 100 requests per minute per user
 *       'conversation.read' => '300,1',  // 300 requests per minute
 *       'voice.inbound' => '10,1',       // 10 calls per minute per company
 *   ]
 */
class RateLimitOmniEndpoints
{
    public function __construct(
        protected RateLimiter $limiter
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $key = $this->resolveRateLimitKey($request);
        $limit = $this->resolveRateLimit($request);

        if ($this->limiter->tooManyAttempts($key, $limit['requests'])) {
            return $this->buildRateLimitResponse($request, $limit);
        }

        $this->limiter->hit($key, $limit['decay']);

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $key, $limit);
    }

    /**
     * Determine the rate limit key based on user/company context.
     */
    protected function resolveRateLimitKey(Request $request): string
    {
        $companyId = $request->input('company_id') ?? auth()->user()?->company_id ?? 'anonymous';
        $userId = auth()->id() ?? 'anonymous';
        $endpoint = $request->route()?->getName() ?? 'unknown';

        return "omni:{$endpoint}:{$companyId}:{$userId}";
    }

    /**
     * Resolve rate limit from configuration or apply defaults.
     */
    protected function resolveRateLimit(Request $request): array
    {
        $route = $request->route()?->getName();

        $limits = config('omni.rate_limits', []);

        // Default to more restrictive limits
        $defaults = [
            'conversation.store' => ['requests' => 100, 'decay' => 1], // 100 per min
            'conversation.read' => ['requests' => 300, 'decay' => 1],  // 300 per min
            'voice.inbound' => ['requests' => 10, 'decay' => 1],       // 10 per min
            'voice.callback' => ['requests' => 5, 'decay' => 1],       // 5 per min
        ];

        $configured = $limits[$route] ?? null;
        if ($configured && is_array($configured)) {
            return $configured;
        }

        return $defaults[$route] ?? ['requests' => 60, 'decay' => 1]; // Global fallback
    }

    /**
     * Build rate limit exceeded response.
     */
    protected function buildRateLimitResponse(Request $request, array $limit): SymfonyResponse
    {
        $retryAfter = $this->limiter->availableIn($this->resolveRateLimitKey($request));

        return response()->json([
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'message' => 'Too many requests. Please try again later.',
                'status' => 429,
                'retry_after_seconds' => $retryAfter,
                'limit' => $limit['requests'],
                'window_minutes' => $limit['decay'],
            ],
        ], 429)
            ->header('Retry-After', $retryAfter)
            ->header('X-RateLimit-Limit', $limit['requests'])
            ->header('X-RateLimit-Window', "{$limit['decay']}m");
    }

    /**
     * Add rate limit info to response headers.
     */
    protected function addRateLimitHeaders(SymfonyResponse $response, string $key, array $limit): SymfonyResponse
    {
        $remaining = $limit['requests'] - $this->limiter->attempts($key);
        $resetAt = now()->addMinutes($limit['decay'])->timestamp;

        return $response
            ->header('X-RateLimit-Limit', $limit['requests'])
            ->header('X-RateLimit-Remaining', max(0, $remaining))
            ->header('X-RateLimit-Reset', $resetAt);
    }
}

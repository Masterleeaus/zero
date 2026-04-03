<?php

namespace App\Http\Middleware\Titan;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Rate-limit MCP endpoint calls per API key.
 *
 * Default: 100 calls per minute per key.
 * Configurable via titan_core.mcp.rate_limit (requests per minute).
 */
class McpRateLimitMiddleware
{
    private const RATE_LIMIT_WINDOW_SECONDS = 60;

    public function __construct(protected RateLimiter $limiter)
    {
    }

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $maxPerMinute = (int) config('titan_core.mcp.rate_limit', 100);
        $key = 'mcp:' . ($request->header('X-MCP-Key') ?? $request->ip());

        if ($this->limiter->tooManyAttempts($key, $maxPerMinute)) {
            return response()->json([
                'error'       => 'Too Many Requests',
                'retry_after' => $this->limiter->availableIn($key),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key, self::RATE_LIMIT_WINDOW_SECONDS);

        return $next($request);
    }
}

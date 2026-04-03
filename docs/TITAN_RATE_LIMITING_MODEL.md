# TITAN RATE LIMITING MODEL

## Overview

TitanCore applies rate limiting at two layers:

1. **MCP Layer** – `McpRateLimitMiddleware` (per API key)
2. **AI Router Layer** – `TitanAIRateLimitMiddleware` (per user + per company budget)

## Middleware Registration

Aliases registered in `app/Http/Kernel.php`:

```php
'titan.mcp.throttle' => McpRateLimitMiddleware::class,
'titan.ai.throttle'  => TitanAIRateLimitMiddleware::class,
```

## MCP Rate Limiting

**Middleware**: `App\Http\Middleware\Titan\McpRateLimitMiddleware`

**Default**: 100 MCP calls per minute per API key (or IP if no key present).

**Config key**: `titan_core.mcp.rate_limit` (override via `MCP_RATE_LIMIT` env var)

**Cache key format**: `mcp:{X-MCP-Key or IP}`

**Response on limit exceeded**:
```json
{
    "error": "Too Many Requests",
    "retry_after": 42
}
```
HTTP 429.

**Attach to**: `/mcp` routes.

## TitanAI Rate Limiting

**Middleware**: `App\Http\Middleware\Titan\TitanAIRateLimitMiddleware`

**Checks**:
1. Per-user: 60 AI requests per minute (configurable via `titan_core.ai.rate_limit_per_user`)
2. Per-company daily: token cap from `titan_budgets.per_company_daily` (0 = unlimited)

**Cache key format**:
- `titan-ai:user:{user_id}` — 60 second window
- `titan-ai:company:{company_id}:daily` — 86400 second window

**Response on limit exceeded**:
```json
{
    "error": "Rate limit exceeded for your account.",
    "retry_after": 15
}
```
HTTP 429.

**Attach to**: `TitanAIRouter` execution routes and skill dispatch endpoints.

## Budget Enforcement

Budget caps are configured in `config/titan_budgets.php` (managed via Admin Panel → Budgets):

| Setting | Default | Purpose |
|---------|---------|---------|
| `daily_limit` | 0 | Platform-wide daily token cap |
| `per_request_max` | 4096 | Per-request token hard cap |
| `per_user_daily` | 0 | Per-user daily token cap |
| `per_company_daily` | 0 | Per-company daily token cap |
| `on_budget_exceeded` | `deny` | Action: deny / fallback_model / notify_admin |

## Applying to Routes

```php
Route::middleware(['auth', 'titan.ai.throttle'])->group(function () {
    // AI execution routes
});

Route::middleware(['titan.mcp.throttle'])->prefix('mcp')->group(function () {
    // MCP tool endpoints
});
```

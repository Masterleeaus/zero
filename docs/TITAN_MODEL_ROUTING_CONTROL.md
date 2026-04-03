# TITAN MODEL ROUTING CONTROL

## Overview

`TitanAIRouter` (`app/TitanCore/Zero/AI/TitanAIRouter.php`) is the canonical AI execution path. All AI requests must pass through this router.

## Configuration

Model routing is configured in `config/titan_ai.php` (managed via Admin Panel → Models).

### Default Models

```php
'default_text_model'  => env('TITAN_DEFAULT_TEXT_MODEL', 'gpt-4o'),
'default_image_model' => env('TITAN_DEFAULT_IMAGE_MODEL', 'dall-e-3'),
```

### Per-Intent Overrides

```php
'intents' => [
    'text.complete'    => 'gpt-4o',
    'image.generate'   => 'dall-e-3',
    'voice.synthesize' => 'tts-1',
    'agent.task'       => 'gpt-4o',
    'code.assist'      => 'gpt-4o',
],
```

### Fallback Chain

```php
'fallback_chain' => ['openai', 'gemini', 'deepseek'],
```

## Routing Pipeline

```
Titan Omni → TitanAIRouter → TitanMemory → Signals → Pulse → Approval → Execution → Rewind
```

## Budget Gate

Before any provider execution, `TitanAIRateLimitMiddleware` checks:
1. Per-user rate limit (60 req/min default, configurable via `titan_core.ai.rate_limit_per_user`)
2. Per-company daily token cap (from `titan_budgets.per_company_daily`)

If exceeded, the request is rejected with `HTTP 429`.

## Envelope Normalisation

The router always ensures `company_id` is present. If only `team_id` is provided, it is promoted to `company_id`.

## Status Endpoint

`GET /dashboard/user/business-suite/core/runtime` returns router capability status as JSON.

## Environment Variables

| Variable | Default | Purpose |
|----------|---------|---------|
| `TITAN_DEFAULT_TEXT_MODEL` | `gpt-4o` | Default text completion model |
| `TITAN_DEFAULT_IMAGE_MODEL` | `dall-e-3` | Default image generation model |
| `TITAN_CORE_MODEL_ROUTER` | `zero` | Router strategy identifier |
| `TITAN_BYO_KEY_ENABLED` | `false` | Allow user-supplied API keys |
| `TITAN_AI_RATE_LIMIT_PER_USER` | `60` | Max AI requests per minute per user |

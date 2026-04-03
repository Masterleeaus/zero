<?php
return [
  'provider' => env('AI_PROVIDER', 'openai'),
  'enabled' => env('AI_ENABLED', true),

  // OpenAI block; api_key is sourced from .env or config cache
  'openai' => [
    'api_key' => env('OPENAI_API_KEY', ''),
    'base_uri' => env('OPENAI_BASE', 'https://api.openai.com/v1'),
  ],

  // Defaults
  'default_model' => env('AI_MODEL', 'gpt-4o-mini'),
  'budget' => [
    'max_forecast_months' => env('BUDGET_AI_MAX_MONTHS', 12),
    'safe_mode' => env('BUDGET_AI_SAFE_MODE', true)
  ]
];
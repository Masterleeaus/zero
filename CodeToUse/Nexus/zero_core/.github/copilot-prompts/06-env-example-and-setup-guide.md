# Copilot Task: Create .env.example and Setup Guide

## Context
This Laravel 10 SaaS has no `.env.example` file. New developers and deployment pipelines have no reference for required environment variables.

## Your Task

### 1. Create `.env.example` at the project root

Scan these files to extract all `env('...')` calls and build the `.env.example`:
```bash
grep -rh "env('" config/ --include="*.php" | grep -oP "env\('\K[^']*" | sort -u
```

The file must include (at minimum) these sections with placeholder values:

```env
# Application
APP_NAME="MagicAI"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
APP_DOMAIN=localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=magicai
DB_USERNAME=root
DB_PASSWORD=

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# AI Providers (fill at least one)
OPENAI_API_KEY=
OPENAI_ORGANIZATION=
OPENAI_BASE_URL=
GEMINI_API_KEY=
DEEPSEEK_API_KEY=
XAI_API_KEY=
AWS_BEDROCK_KEY=
AWS_BEDROCK_SECRET=
AWS_BEDROCK_REGION=us-east-1

# Storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Broadcasting
BROADCAST_DRIVER=log
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
ABLY_KEY=

# Payment Gateways (configure as needed)
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=sandbox
PADDLE_CLIENT_SIDE_TOKEN=
PADDLE_API_KEY=
PADDLE_WEBHOOK_SECRET=
RAZORPAY_KEY=
RAZORPAY_SECRET=
COINGATE_API_KEY=
COINGATE_ENVIRONMENT=sandbox
YOOKASSA_SHOP_ID=
YOOKASSA_SECRET_KEY=
IYZICO_API_KEY=
IYZICO_SECRET_KEY=
IYZICO_BASE_URL=

# Social / Messaging
TELEGRAM_BOT_TOKEN=
TWILIO_SID=
TWILIO_AUTH_TOKEN=
TWILIO_FROM=
TWITTER_CONSUMER_KEY=
TWITTER_CONSUMER_SECRET=
TWITTER_ACCESS_TOKEN=
TWITTER_ACCESS_TOKEN_SECRET=

# ElevenLabs TTS
ELEVENLABS_API_KEY=

# Sentry (Error Tracking)
SENTRY_LARAVEL_DSN=

# MagicAI License
MAGICAI_LICENSE_KEY=
```

### 2. Update `INSTALL.md`
Add a "Local Development Setup" section:
```markdown
## Local Development Setup

1. Clone the repo
2. `cp .env.example .env`
3. Fill in required values in `.env` (at minimum: APP_KEY, DB_*, one AI provider key)
4. `composer install --ignore-platform-req=ext-redis`
5. `php artisan key:generate`
6. `php artisan migrate`
7. `php artisan db:seed` (optional demo data)
8. `npm install && npm run dev`
9. `php artisan serve`
```

## Constraints
- Placeholder values must NOT be real credentials
- Use `your-value-here` or empty string for secrets
- Add inline comments explaining non-obvious variables

# TITAN QUEUE ARCHITECTURE

## Dedicated Queues

TitanCore uses three dedicated database queues, separated by concern:

| Queue | Purpose | `retry_after` |
|-------|---------|---------------|
| `titan-ai` | AI provider calls, completions, router decisions | 120s |
| `titan-signals` | Signal callbacks and dispatch events | 90s |
| `titan-skills` | Zylos skill execution jobs | 180s |

All three are backed by the `database` driver using the `jobs` table.

## Configuration

Defined in `config/queue.php` under `connections`:

```php
'titan-ai' => [
    'driver'      => 'database',
    'table'       => 'jobs',
    'queue'       => 'titan-ai',
    'retry_after' => 120,
],
'titan-signals' => [
    'driver'      => 'database',
    'table'       => 'jobs',
    'queue'       => 'titan-signals',
    'retry_after' => 90,
],
'titan-skills' => [
    'driver'      => 'database',
    'table'       => 'jobs',
    'queue'       => 'titan-skills',
    'retry_after' => 180,
],
```

## Worker Launch Commands

Start dedicated workers on your server:

```bash
# AI completions and router decisions
php artisan queue:work --queue=titan-ai

# Signal callbacks
php artisan queue:work --queue=titan-signals

# Skill executions
php artisan queue:work --queue=titan-skills
```

For production, wrap each in a supervisor process or PM2:

```ini
[program:titan-ai-worker]
command=php /path/to/artisan queue:work --queue=titan-ai --sleep=3 --tries=3
autostart=true
autorestart=true
```

## Admin Dashboard

View real-time queue stats at: `/dashboard/admin/titan/core/queues`

Actions available:
- **Retry failed** – calls `queue:retry --queue=<name>`
- **Flush** – deletes all pending jobs from the named queue

## Failed Jobs

Failed jobs are stored in the `failed_jobs` table. The dashboard shows per-queue failed job counts sourced from this table.

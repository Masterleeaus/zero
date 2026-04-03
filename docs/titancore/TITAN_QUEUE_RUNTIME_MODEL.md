# TITAN QUEUE RUNTIME MODEL

**Version:** Prompt 6  
**Status:** Phase 6.7 Verified

---

## Queue Isolation

Titan Core uses three dedicated queues to prevent cross-contamination and allow independent worker scaling.

| Queue | Connection | Workload | retry_after |
|-------|-----------|---------|-------------|
| `titan-ai` | database | AI completions, memory writes | 120s |
| `titan-signals` | database | Signal dispatch callbacks | 90s |
| `titan-skills` | database | Zylos skill execution | 300s |

All three connections use the `jobs` table (same as the default queue). The queue name is the isolation boundary.

---

## Worker Start Commands

```bash
# AI completions worker
php artisan queue:work titan-ai --queue=titan-ai --tries=3 --timeout=90 --sleep=3

# Signal dispatch worker
php artisan queue:work titan-signals --queue=titan-signals --tries=3 --timeout=60 --sleep=3

# Skill execution worker (longer timeout for external calls)
php artisan queue:work titan-skills --queue=titan-skills --tries=3 --timeout=240 --sleep=5
```

---

## PM2 Ecosystem

```js
// ecosystem.config.cjs (relevant section)
{
  name: 'titan-ai-worker',
  script: 'php',
  args: 'artisan queue:work titan-ai --queue=titan-ai --tries=3 --timeout=90',
  interpreter: 'none',
  autorestart: true,
},
{
  name: 'titan-signals-worker',
  script: 'php',
  args: 'artisan queue:work titan-signals --queue=titan-signals --tries=3 --timeout=60',
  interpreter: 'none',
  autorestart: true,
},
{
  name: 'titan-skills-worker',
  script: 'php',
  args: 'artisan queue:work titan-skills --queue=titan-skills --tries=3 --timeout=240',
  interpreter: 'none',
  autorestart: true,
},
```

---

## Queue Configuration (config/queue.php)

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
    'retry_after' => 300,
],
```

---

## Failed Jobs

```bash
# Check failed jobs across all queues
php artisan queue:failed

# Retry a specific failed job
php artisan queue:retry {id}

# Retry all failed Titan jobs
php artisan queue:retry --queue=titan-ai
php artisan queue:retry --queue=titan-signals
php artisan queue:retry --queue=titan-skills

# Flush failed jobs (caution)
php artisan queue:flush
```

---

## Worker Restart (Safe)

```bash
# Signal workers to restart after current job completes
php artisan queue:restart

# Via PM2
pm2 restart titan-ai-worker
pm2 restart titan-signals-worker
pm2 restart titan-skills-worker
```

Queue workers read a restart signal from the cache. This is safe mid-operation.

---

## Cross-Queue Contamination Prevention

- AI completion jobs MUST specify `->onQueue('titan-ai')`.
- Signal dispatch jobs MUST specify `->onQueue('titan-signals')`.
- Skill execution jobs MUST specify `->onQueue('titan-skills')`.
- The default `database` connection's `default` queue is reserved for host application jobs.
- Titan queue workers do NOT process the `default` queue.

---

## Monitoring

Check worker health:
```bash
php artisan queue:monitor titan-ai:100,titan-signals:50,titan-skills:25
```

This alerts when queue depth exceeds the threshold.

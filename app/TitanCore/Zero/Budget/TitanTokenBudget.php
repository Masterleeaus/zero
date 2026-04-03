<?php

namespace App\TitanCore\Zero\Budget;

use Illuminate\Support\Facades\Cache;

/**
 * TitanTokenBudget — enforces per-user, per-company, per-intent and daily token limits.
 *
 * Usage is tracked in the cache store. When a limit is exceeded the router
 * blocks execution and emits a titan.core.activity event with status=blocked.
 */
class TitanTokenBudget
{
    public function dailyLimit(): int
    {
        return (int) config('titan_core.budget.daily_limit', 100000);
    }

    public function perRequestLimit(): int
    {
        return (int) config('titan_core.budget.per_request_limit', 4096);
    }

    public function perUserDailyLimit(): int
    {
        return (int) config('titan_core.budget.per_user_daily_limit', 10000);
    }

    public function perCompanyDailyLimit(): int
    {
        return (int) config('titan_core.budget.per_company_daily_limit', 50000);
    }

    /**
     * Check whether the given request is within all budget limits.
     *
     * @param  array<string, mixed>  $envelope
     */
    public function isAllowed(array $envelope): bool
    {
        $tokens    = (int) ($envelope['tokens'] ?? $this->perRequestLimit());
        $companyId = (int) ($envelope['company_id'] ?? 0);
        $userId    = (int) ($envelope['user_id'] ?? 0);
        $intent    = (string) ($envelope['intent'] ?? 'default');

        if ($tokens > $this->perRequestLimit()) {
            return false;
        }

        if ($companyId > 0 && $this->companyUsageToday($companyId) + $tokens > $this->perCompanyDailyLimit()) {
            return false;
        }

        if ($userId > 0 && $this->userUsageToday($userId) + $tokens > $this->perUserDailyLimit()) {
            return false;
        }

        if ($this->globalUsageToday() + $tokens > $this->dailyLimit()) {
            return false;
        }

        return true;
    }

    /**
     * Record token usage after a successful completion.
     *
     * @param  array<string, mixed>  $envelope
     */
    public function record(array $envelope, int $tokensUsed): void
    {
        $companyId = (int) ($envelope['company_id'] ?? 0);
        $userId    = (int) ($envelope['user_id'] ?? 0);

        $this->increment('titan.budget.global.'.now()->format('Y-m-d'), $tokensUsed);

        if ($companyId > 0) {
            $this->increment("titan.budget.company.{$companyId}.".now()->format('Y-m-d'), $tokensUsed);
        }

        if ($userId > 0) {
            $this->increment("titan.budget.user.{$userId}.".now()->format('Y-m-d'), $tokensUsed);
        }
    }

    public function globalUsageToday(): int
    {
        return (int) Cache::get('titan.budget.global.'.now()->format('Y-m-d'), 0);
    }

    public function companyUsageToday(int $companyId): int
    {
        return (int) Cache::get("titan.budget.company.{$companyId}.".now()->format('Y-m-d'), 0);
    }

    public function userUsageToday(int $userId): int
    {
        return (int) Cache::get("titan.budget.user.{$userId}.".now()->format('Y-m-d'), 0);
    }

    /**
     * Return a budget status snapshot.
     *
     * @param  array<string, mixed>  $envelope
     * @return array<string, mixed>
     */
    public function status(array $envelope): array
    {
        $companyId = (int) ($envelope['company_id'] ?? 0);
        $userId    = (int) ($envelope['user_id'] ?? 0);

        return [
            'global_today'  => $this->globalUsageToday(),
            'global_limit'  => $this->dailyLimit(),
            'company_today' => $companyId > 0 ? $this->companyUsageToday($companyId) : null,
            'company_limit' => $this->perCompanyDailyLimit(),
            'user_today'    => $userId > 0 ? $this->userUsageToday($userId) : null,
            'user_limit'    => $this->perUserDailyLimit(),
            'per_request'   => $this->perRequestLimit(),
        ];
    }

    private function increment(string $key, int $amount): void
    {
        // 25 hours so midnight rollover is safe
        $ttl     = 25 * 3600;
        $current = (int) Cache::get($key, 0);
        Cache::put($key, $current + $amount, $ttl);
    }
}

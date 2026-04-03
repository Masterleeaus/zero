<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\Security\BlacklistEmail;
use App\Models\Security\BlacklistIp;
use App\Models\Security\CyberSecurityConfig;

/**
 * CyberSecurityConfigService
 *
 * Manages the singleton CyberSecurityConfig row and provides helper methods
 * for checking blacklists used by the security middleware stack.
 */
class CyberSecurityConfigService
{
    public function getConfig(): CyberSecurityConfig
    {
        return CyberSecurityConfig::singleton();
    }

    public function updateLoginProtection(array $data): CyberSecurityConfig
    {
        $config = $this->getConfig();

        $config->fill(array_intersect_key($data, array_flip([
            'max_retries',
            'lockout_time',
            'max_lockouts',
            'extended_lockout_time',
            'reset_retries',
            'alert_after_lockouts',
            'email',
            'user_timeout',
            'ip_check',
            'ip',
        ])));

        $config->save();

        return $config;
    }

    public function updateSessionPolicy(bool $uniqueSession): CyberSecurityConfig
    {
        $config = $this->getConfig();
        $config->unique_session = $uniqueSession;
        $config->save();

        return $config;
    }

    public function isIpBlacklisted(string $ip): bool
    {
        return BlacklistIp::where('ip_address', $ip)->exists();
    }

    public function isEmailBlacklisted(string $email): bool
    {
        if (! str_contains($email, '@')) {
            return BlacklistEmail::where('email', $email)->exists();
        }

        $domain = '@' . str($email)->after('@')->toString();

        return BlacklistEmail::where('email', $email)
            ->orWhere('email', $domain)
            ->exists();
    }

    public function addBlacklistIp(string $ip): BlacklistIp
    {
        return BlacklistIp::firstOrCreate(['ip_address' => $ip]);
    }

    public function addBlacklistEmail(string $email): BlacklistEmail
    {
        return BlacklistEmail::firstOrCreate(['email' => $email]);
    }

    public function removeBlacklistIp(int $id): void
    {
        BlacklistIp::destroy($id);
    }

    public function removeBlacklistEmail(int $id): void
    {
        BlacklistEmail::destroy($id);
    }
}

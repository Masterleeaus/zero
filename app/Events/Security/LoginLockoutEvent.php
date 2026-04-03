<?php

declare(strict_types=1);

namespace App\Events\Security;

use Illuminate\Queue\SerializesModels;

/**
 * Fired when an IP address has exceeded the configured lockout threshold.
 *
 * Listeners may send an alert email to the security admin.
 */
class LoginLockoutEvent
{
    use SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $ip,
    ) {}
}

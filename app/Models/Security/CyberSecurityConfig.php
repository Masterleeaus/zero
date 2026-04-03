<?php

declare(strict_types=1);

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;

/**
 * System-wide cybersecurity configuration.
 *
 * Single-row settings table: login retry limits, lockout timings,
 * alerting email, unique-session enforcement, and IP-check mode.
 *
 * @property int    $id
 * @property int    $max_retries
 * @property string|null $email
 * @property int    $lockout_time
 * @property int    $max_lockouts
 * @property int    $extended_lockout_time
 * @property int    $reset_retries
 * @property int    $alert_after_lockouts
 * @property int    $user_timeout
 * @property bool   $ip_check
 * @property string|null $ip
 * @property bool   $unique_session
 */
class CyberSecurityConfig extends Model
{
    protected $table = 'cyber_securities';

    protected $guarded = ['id'];

    protected $casts = [
        'ip_check'       => 'boolean',
        'unique_session' => 'boolean',
    ];

    /** Return the singleton row, creating it with defaults when absent. */
    public static function singleton(): static
    {
        return static::firstOrCreate([], [
            'max_retries'           => 3,
            'lockout_time'          => 2,
            'max_lockouts'          => 3,
            'extended_lockout_time' => 1,
            'reset_retries'         => 24,
            'alert_after_lockouts'  => 2,
            'user_timeout'          => 10,
            'ip_check'              => false,
            'unique_session'        => false,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;

/**
 * System-wide IP address blacklist.
 *
 * @property int    $id
 * @property string $ip_address
 */
class BlacklistIp extends Model
{
    protected $table = 'blacklist_ips';

    protected $guarded = ['id'];
}

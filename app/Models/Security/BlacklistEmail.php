<?php

declare(strict_types=1);

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;

/**
 * System-wide email / email-domain blacklist.
 *
 * Entries may be a full address (user@example.com) or a domain
 * prefix (@example.com) to block an entire domain.
 *
 * @property int    $id
 * @property string $email
 */
class BlacklistEmail extends Model
{
    protected $table = 'blacklist_emails';

    protected $guarded = ['id'];
}

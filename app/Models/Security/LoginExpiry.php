<?php

declare(strict_types=1);

namespace App\Models\Security;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user login expiry date.
 *
 * When the current date is past expiry_date the LoginExpiryMiddleware
 * forces a logout for that user.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property \Illuminate\Support\Carbon      $expiry_date
 */
class LoginExpiry extends Model
{
    protected $table = 'login_expiries';

    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

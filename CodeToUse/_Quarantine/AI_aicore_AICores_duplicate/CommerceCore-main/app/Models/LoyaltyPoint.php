<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    use \App\Traits\BelongsToStore;

    protected $fillable = ['user_id', 'points', 'type', 'reason'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

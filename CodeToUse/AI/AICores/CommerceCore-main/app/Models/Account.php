<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Account extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'type', 'balance', 'account_number', 'bank_name', 'is_active'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

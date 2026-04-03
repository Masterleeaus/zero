<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory;

    protected $fillable = [
        'store_id', 'name', 'email', 'phone', 'company', 'address', 'status',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}

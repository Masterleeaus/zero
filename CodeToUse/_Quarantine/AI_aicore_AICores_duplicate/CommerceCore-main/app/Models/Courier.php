<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory;

    protected $fillable = ['name', 'phone', 'email', 'balance', 'is_active'];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function payments()
    {
        return $this->hasMany(CourierPayment::class);
    }
}

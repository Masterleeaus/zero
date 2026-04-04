<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierPayment extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory;

    protected $fillable = ['courier_id', 'amount', 'type', 'reference', 'notes'];

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }
}

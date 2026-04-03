<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class CallNote extends Model
{
    protected $table = 'titanhello_call_notes';

    protected $fillable = [
        'call_id',
        'user_id',
        'note',
    ];

    protected $casts = [
        'call_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class, 'call_id');
    }
}

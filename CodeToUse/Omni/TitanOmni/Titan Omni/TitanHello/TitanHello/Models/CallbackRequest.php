<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class CallbackRequest extends Model
{
    protected $table = 'titanhello_callback_requests';

    protected $fillable = [
        'company_id',
        'call_id',
        'from_number',
        'to_number',
        'assigned_to',
        'status',       // open|done|cancelled
        'priority',     // low|normal|high|urgent
        'due_at',
        'note',
        'created_by',
        'closed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class, 'call_id');
    }
}

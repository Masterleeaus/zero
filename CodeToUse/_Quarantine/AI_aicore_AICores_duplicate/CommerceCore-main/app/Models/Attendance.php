<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Attendance extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory, LogsActivity;

    protected $fillable = ['user_id', 'date', 'clock_in', 'clock_out', 'ip_address'];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

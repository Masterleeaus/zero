<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmniAnalytic extends Model
{
    use HasFactory;

    protected $table = 'omni_analytics';

    protected $fillable = [
        'company_id',
        'agent_id',
        'metric_date',
        'channel',
        'metric_key',
        'metric_value',
        'metadata',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'metric_value' => 'float',
        'metadata' => 'array',
    ];
}

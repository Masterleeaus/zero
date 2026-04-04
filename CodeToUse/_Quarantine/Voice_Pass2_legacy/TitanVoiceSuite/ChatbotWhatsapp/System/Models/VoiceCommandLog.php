<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoiceCommandLog extends Model
{
    use HasFactory;

    protected $table = 'voice_command_logs';

    protected $guarded = [];

    protected $casts = [
        'entities' => 'array',
        'confidence' => 'float',
    ];
}

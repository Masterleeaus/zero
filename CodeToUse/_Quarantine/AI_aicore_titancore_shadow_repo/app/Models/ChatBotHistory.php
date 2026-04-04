<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ChatBotHistory extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'chatbot_history';
}

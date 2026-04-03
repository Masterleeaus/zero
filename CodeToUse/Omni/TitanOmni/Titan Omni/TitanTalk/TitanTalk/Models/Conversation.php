<?php
namespace Modules\TitanTalk\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model {
    protected $table = 'ai_converse_conversations';

    protected $fillable = [
        'tenant_id',
        'channel',
        'external_ref',
        'context',
        'client_id',
        'lead_id',
        'project_id',
        'ticket_id',
        'task_id',
        'invoice_id',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }
}

<?php
namespace Modules\TitanTalk\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    protected $table = 'ai_converse_messages';
    protected $fillable = ['tenant_id','conversation_id','sender','text','meta'];
    protected $casts = ['meta'=>'array'];
    public function conversation(){ return $this->belongsTo(Conversation::class, 'conversation_id'); }
}

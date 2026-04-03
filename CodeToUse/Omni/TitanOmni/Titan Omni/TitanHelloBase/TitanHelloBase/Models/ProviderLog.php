<?php
namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderLog extends Model {
    protected $table = 'ai_converse_provider_logs';
    protected $fillable = ['tenant_id','conversation_id','direction','payload','meta'];
    protected $casts = ['payload'=>'array','meta'=>'array'];
}

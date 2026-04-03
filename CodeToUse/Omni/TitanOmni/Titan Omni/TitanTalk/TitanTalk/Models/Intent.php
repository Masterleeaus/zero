<?php
namespace Modules\TitanTalk\Models;

use Illuminate\Database\Eloquent\Model;

class Intent extends Model {
    protected $table = 'ai_converse_intents';
    protected $fillable = ['tenant_id','name','description','metadata'];
    protected $casts = ['metadata'=>'array'];
}

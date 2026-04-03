<?php
namespace Modules\TitanTalk\Models;

use Illuminate\Database\Eloquent\Model;

class Dialog extends Model {
    protected $table = 'ai_converse_dialogs';
    protected $fillable = ['tenant_id','name','graph','metadata'];
    protected $casts = ['graph'=>'array','metadata'=>'array'];
}

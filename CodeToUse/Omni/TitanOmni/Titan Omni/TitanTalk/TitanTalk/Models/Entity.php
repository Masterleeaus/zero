<?php
namespace Modules\TitanTalk\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model {
    protected $table = 'ai_converse_entities';
    protected $fillable = ['tenant_id','name','values','metadata'];
    protected $casts = ['values'=>'array','metadata'=>'array'];
}

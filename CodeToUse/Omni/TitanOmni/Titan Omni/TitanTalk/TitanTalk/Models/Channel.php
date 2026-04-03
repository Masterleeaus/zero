<?php
namespace Modules\TitanTalk\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model {
    protected $table = 'ai_converse_channels';
    protected $fillable = ['tenant_id','name','driver','config','enabled'];
    protected $casts = ['config'=>'array','enabled'=>'boolean'];
}

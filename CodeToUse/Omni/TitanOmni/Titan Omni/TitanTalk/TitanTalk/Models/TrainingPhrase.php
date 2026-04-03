<?php
namespace Modules\TitanTalk\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingPhrase extends Model {
    protected $table = 'ai_converse_training_phrases';
    protected $fillable = ['tenant_id','intent_id','text','metadata'];
    protected $casts = ['metadata'=>'array'];
    public function intent(){ return $this->belongsTo(Intent::class, 'intent_id'); }
}

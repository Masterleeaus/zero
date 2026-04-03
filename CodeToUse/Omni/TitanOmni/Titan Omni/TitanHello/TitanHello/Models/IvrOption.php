<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IvrOption extends Model
{
    protected $table = 'titanhello_ivr_options';

    protected $fillable = [
        'ivr_menu_id',
        'dtmf', // 0-9, *, #
        'label',
        'action_type', // ring_group | voicemail | hangup
        'action_target_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'action_target_id' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(IvrMenu::class, 'ivr_menu_id');
    }
}

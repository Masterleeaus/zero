<?php

namespace Modules\TrWorkPermits\Entities;

use App\Models\BaseModel;
use App\Traits\IconTrait;
use Modules\TrWorkPermits\Entities\WorkPermits;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPermitsFile extends BaseModel
{
    use IconTrait;
    protected $table = 'tr_workpermit_files';
    protected $guarded = ['id'];

    const FILE_PATH = 'workpermit-files';

    protected $appends = ['file_url', 'icon'];

    public function getFileUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('workpermit-files/' . $this->wp_id . '/' . $this->hashname);
    }

    public function wp(): BelongsTo
    {
        return $this->belongsTo(WorkPermits::class);
    }

}

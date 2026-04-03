<?php
namespace Modules\Engineerings\Entities;

use App\Models\BaseModel;
use App\Traits\IconTrait;
use Modules\Engineerings\Entities\WorkOrder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderFile extends BaseModel
{
    use IconTrait;
    protected $table = 'workorders_files';
    protected $guarded = ['id'];

    const FILE_PATH = 'workorder-files';

    protected $appends = ['file_url', 'icon'];

    public function getFileUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('workorder-files/' . $this->workorder_id . '/' . $this->hashname);
    }

    public function wo(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

}

<?php

namespace Modules\FieldItems\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemFiles extends BaseModel
{
    use HasCompany;
    use IconTrait;

    const FILE_PATH = 'items';

    protected $fillable = [];

    protected $guarded = ['id'];

    protected $appends = ['file_url', 'icon'];

    public $timestamps = false;

    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(Item::FILE_PATH . '/' . $this->hashname);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

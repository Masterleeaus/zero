<?php
namespace Modules\Engineerings\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Modules\Engineerings\Entities\Services;

class WorkServices extends BaseModel
{
    use HasCompany;

    protected $table = 'workrequests_services';
    protected $guarded = ['id'];

    public function service()
    {
        return $this->belongsTo(Services::class, 'services_id');
    }
}


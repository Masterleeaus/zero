<?php

namespace Modules\Engineerings\Entities;

use App\Models\User;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\ModuleSetting;
use Modules\Units\Entities\Unit;
use App\Traits\CustomFieldsTrait;
use Modules\Complaint\Entities\Complaint;
use Modules\Engineerings\Entities\WorkItems;
use Modules\Engineerings\Entities\WorkServices;
use Modules\Accountings\Entities\JournalType;
use Modules\Houses\Entities\House;

class WorkRequest extends BaseModel
{
    use CustomFieldsTrait;
    use HasCompany;
    protected $table = 'workrequests';
    protected $guarded = ['id'];

    public function ticket()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    public function items()
    {
        return $this->hasMany(WorkItems::class, 'workrequest_id');
    }

    public function services()
    {
        return $this->hasMany(WorkServices::class, 'workrequest_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    public static function lastInvoiceNumber()
    {
        return (int)WorkRequest::max('id');
    }
}

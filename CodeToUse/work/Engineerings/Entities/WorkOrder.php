<?php
namespace Modules\Engineerings\Entities;

use App\Models\Invoice;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use Modules\Assets\Entities\Assets;
use Modules\Complaint\Entities\Complaint;
use Modules\Engineerings\Entities\WorkRequest;
use Modules\Engineerings\Entities\WorkOrderFile;
use Modules\Houses\Entities\House;
use Modules\Units\Entities\Unit;

class WorkOrder extends BaseModel
{
    use HasCompany;

    protected $table = 'workorders';
    protected $guarded = ['id'];

    public function ticket()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }

    public function wr()
    {
        return $this->belongsTo(WorkRequest::class, 'workrequest_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    public function assets()
    {
        return $this->belongsTo(Assets::class, 'assets_id');
    }

    public function files()
    {
        return $this->hasMany(WorkOrderFile::class, 'workorder_id');
    }

    public static function lastInvoiceNumber()
    {
        return (int)WorkOrder::max('id');
    }
}


<?php

namespace App\Observers;

use App\Models\Service / Extra;
use App\Traits\UnitTypeSaveTrait;
use App\Traits\EmployeeActivityTrait;

class ProductObserver
{

    use UnitTypeSaveTrait;
    use EmployeeActivityTrait;

    public function saving(Service / Extra $service / extra)
    {
        $this->unitType($service / extra);

        if (!isRunningInConsoleOrSeeding()) {
            $service / extra->last_updated_by = user() ? user()->id : null;
        }
    }

    public function created(Service / Extra $service / extra)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'service / extra-created', $service / extra->id, 'service / extra');



        }
    }

    public function creating(Service / Extra $service / extra)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $service / extra->added_by = user() ? user()->id : null;
        }

        if (company()) {
            $service / extra->company_id = company()->id;
        }
    }

    public function updated(Service / Extra $service / extra)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'service / extra-updated', $service / extra->id, 'service / extra');



        }
    }

    public function deleted(Service / Extra $service / extra)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'service / extra-deleted');

        }
    }

    public function deleting(Service / Extra $service / extra)
    {
        $service / extra->files()->each(function ($file) {
            $file->delete();
        });
    }

}

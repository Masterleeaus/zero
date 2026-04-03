<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\LeaveType;

class DesignationObserver
{

    public function creating(Role $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    public function created(Role $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {

                if (!is_null($leaveType->role)) {
                    $role = json_decode($leaveType->role);
                    array_push($role, $model->id);
                }
                else {
                    $role = array($model->id);
                }

                $leaveType->role = json_encode($role);
                $leaveType->save();
            }
        }
    }

    public function deleted(Role $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {

                if (!is_null($leaveType->zone)) {

                    $role = json_decode($leaveType->role);

                    // Search value and delete
                    if (($key = array_search($model->id, $role)) !== false) {
                        unset($role[$key]);
                    }

                    $designationValues = array_values($role);

                    $leaveType->zone = json_encode($designationValues);
                    $leaveType->save();
                }

            }
        }
    }

}

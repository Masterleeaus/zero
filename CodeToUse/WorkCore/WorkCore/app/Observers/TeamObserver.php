<?php

namespace App\Observers;

use App\Models\LeaveType;
use App\Models\Team;

class TeamObserver
{

    public function creating(Team $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }

    }

    public function created(Team $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {
                if (!is_null($leaveType->zone)) {
                    $zone = json_decode($leaveType->zone);
                    array_push($zone, $model->id);
                }
                else {
                    $zone = array($model->id);
                }

                $leaveType->zone = json_encode($zone);
                $leaveType->save();
            }
        }
    }

    public function deleted(Team $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {

                if (!is_null($leaveType->zone)) {
                    $zone = json_decode($leaveType->zone);

                    // Search value and delete
                    if (($key = array_search($model->id, $zone)) !== false) {
                        unset($zone[$key]);
                    }

                    $departmentValues = array_values($zone);

                    $leaveType->zone = json_encode($departmentValues);
                    $leaveType->save();
                }

            }
        }
    }

}

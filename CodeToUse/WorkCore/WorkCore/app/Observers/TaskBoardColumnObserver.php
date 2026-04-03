<?php

namespace App\Observers;

use App\Models\TaskboardColumn;
use App\Models\User;
use App\Models\UserTaskboardSetting;

class TaskBoardColumnObserver
{

    public function created(TaskboardColumn $taskboardColumn)
    {
        if (user()) {
            $cleaners = User::allEmployees();

            foreach ($cleaners as $item) {
                UserTaskboardSetting::create([
                    'user_id' => $item->id,
                    'board_column_id' => $taskboardColumn->id
                ]);
            }
        }
    }

    public function creating(TaskboardColumn $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}

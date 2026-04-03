<?php

namespace Modules\Timesheet\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Timesheet\Entities\Timesheet;
use Modules\Timesheet\Http\Resources\TimesheetResource;

class TimesheetApiController extends Controller
{
    public function index()
    {
        abort_unless(\Auth::check(), 403);
        abort_unless(\Auth::user()->isAbleTo('timesheet manage'), 403);

        $rows = Timesheet::query()->forCreator()->latest()->limit(100)->get();

        return TimesheetResource::collection($rows);
    }
}

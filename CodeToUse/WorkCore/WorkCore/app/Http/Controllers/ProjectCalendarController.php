<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Team;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use App\Models\ProjectCategory;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectStatusSetting;

class ProjectCalendarController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projectCalendar';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('sites', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $viewPermission = user()->permission('view_projects');

        if (in_array('customer', user_roles())) {
            $this->customers = User::customer();
        }
        else {
            $this->customers = User::allClients();
            $this->allEmployees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
        }

        $this->categories = ProjectCategory::all();
        $this->zones = Team::all();
        $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();

        if ($request->start && $request->end) {
            $startDate = Carbon::parse($request->start)->format('Y-m-d');
            $endDate = Carbon::parse($request->end)->format('Y-m-d');


            if ($startDate !== null && $endDate !== null) {
                $model = Site::where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween(DB::raw('DATE(sites.`deadline`)'), [$startDate, $endDate]);
                        $q->orWhereBetween(DB::raw('DATE(sites.`start_date`)'), [$startDate, $endDate]);
                        $q->orWhere(function ($q1) use ($startDate, $endDate) {
                            $q1->where('sites.start_date', '<=', $startDate)
                                ->where('sites.deadline', '>=', $endDate);
                            return $q1;
                        });
                });
            }

            if (!is_null($request->categoryId) && $request->categoryId != 'all') {
                $model->where('category_id', $request->categoryId);
            }

            if ($request->pinned == 'pinned') {
                $model->join('pinned', 'pinned.project_id', 'sites.id');
                $model->where('pinned.user_id', user()->id);
            }

            if (!is_null($request->employeeId) && $request->employeeId != 'all') {
                $model->leftJoin('project_members', 'project_members.project_id', 'sites.id')
                    ->selectRaw('sites.id, sites.project_short_code, sites.hash, sites.added_by,
                    sites.project_name, sites.start_date, sites.deadline, sites.client_id,
                    sites.completion_percent, sites.project_budget, sites.currency_id,
                    sites.status');
                $model->where('project_members.user_id', $request->employeeId);
            }

            if (!is_null($request->teamId) && $request->teamId != 'all') {
                $model->where('team_id', $request->teamId);
            }

            if (!is_null($request->clientID) && $request->clientID != 'all') {
                $model->where('sites.client_id', $request->clientID);
            }

            if (!is_null($request->status) && $request->status != 'all') {

                if ($request->status == 'overdue') {
                    $model->where('sites.completion_percent', '!=', 100);

                    if ($request->deadLineStartDate == '' && $request->deadLineEndDate == '') {
                        $model->whereDate('sites.deadline', '<', now(company()->timezone)->toDateString());
                    }
                }
                else {
                    $model->where('sites.status', $request->status);
                }
            }

            if ($request->progress) {
                $progressData = explode(',', $request->progress);
                $model->where(function ($q) use ($progressData) {
                    foreach ($progressData as $progress) {
                        $completionPercent = explode('-', $progress);
                        $q->orWhereBetween('sites.completion_percent', [$completionPercent[0], $completionPercent[1]]);
                    }
                });
            }

            if ($request->searchText != '') {
                $model->where(function ($query) use($request) {
                    $query->where('sites.project_name', 'like', '%' . $request->searchText . '%')
                        ->orWhere('sites.project_short_code', 'like', '%' . $request->searchText . '%'); // site short code
                });
            }

            $model = $model->get();
            $projectData = [];

            foreach ($model as $key => $value) {
                $projectStatus = ProjectStatusSetting::where('status_name', $value->status)->first();
                $projectData[] = [
                    'id' => $value->id,
                    'title' => $value->project_name,
                    'start' => $value->start_date->format('Y-m-d'),
                    'end' => (!is_null($value->deadline) ? $value->deadline->format('Y-m-d') : $value->start_date->format('Y-m-d')),
                    'color' => isset($projectStatus->color) ? $projectStatus->color : '#00b5ff'
                ];
            }

            return $projectData;
        }

        return view('sites.calendar', $this->data);
    }

}

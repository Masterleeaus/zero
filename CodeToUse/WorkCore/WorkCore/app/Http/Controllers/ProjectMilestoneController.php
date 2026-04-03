<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Milestone\StoreMilestone;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\Site;
use App\Models\ProjectMilestone;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;


class ProjectMilestoneController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.sites';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('sites', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $id = request('id');

        $this->site = Site::findOrFail($id);
        $addProjectMilestonePermission = user()->permission('add_project_milestones');
        $site = Site::findOrFail($id);

        abort_403(!($addProjectMilestonePermission == 'all' || $site->project_admin == user()->id));

        return view('sites.milestone.create', $this->data);
    }

    /**
     * @param StoreMilestone $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreMilestone $request)
    {
        $milestone = new ProjectMilestone();
        $milestone->project_id = $request->project_id;
        $milestone->milestone_title = $request->milestone_title;
        $milestone->summary = $request->summary;
        $milestone->cost = ($request->cost == '') ? '0' : $request->cost;
        $milestone->currency_id = $request->currency_id;
        $milestone->status = $request->status;
        $milestone->add_to_budget = $request->add_to_budget;
        $milestone->start_date = $request->start_date == null ? $request->start_date : companyToYmd($request->start_date);
        $milestone->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $milestone->save();

        $site = Site::findOrFail($request->project_id);

        if ($request->add_to_budget == 'yes') {
            $site->project_budget = (!is_null($site->project_budget) ? ($site->project_budget + $milestone->cost) : $milestone->cost);
            $site->currency_id = $request->currency_id;
            $site->save();
        }

        $this->logProjectActivity($site->id, 'team chat.newMilestoneCreated');

        return Reply::success(__('team chat.milestoneSuccess'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->milestone = ProjectMilestone::findOrFail($id);
        $this->currencies = Currency::all();

        return view('sites.milestone.edit', $this->data);
    }

    /**
     * @param StoreMilestone $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreMilestone $request, $id)
    {
        $milestone = ProjectMilestone::findOrFail($id);

        // find the current cost of milestone
        $oldCost = $milestone->getOriginal('cost');

        $milestone->project_id = $request->project_id;
        $milestone->milestone_title = $request->milestone_title;
        $milestone->summary = $request->summary;
        $milestone->cost = ($request->cost == '') ? '0' : $request->cost;
        $milestone->currency_id = $request->currency_id;
        $milestone->status = $request->status;
        $milestone->start_date = $request->start_date == null ? $request->start_date : companyToYmd($request->start_date);
        $milestone->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $milestone->save();

        $site = Site::findOrFail($milestone->project_id);

        // get the latest cost of milestone
        $newCost = $milestone->cost;
        $costDifference = $newCost - $oldCost;

        // Update the site budget if the add_to_budget flag is set to 'yes'
        if ($milestone->add_to_budget == 'yes') {

            // Update site budget
            $site->project_budget += $costDifference;
            $site->save();
        }

        $this->logProjectActivity($milestone->project_id, 'team chat.milestoneUpdated');

        return Reply::success(__('team chat.milestoneSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $milestone = ProjectMilestone::findOrFail($id);

        // Retrieve the associated site
        $site = Site::findOrFail($milestone->project_id);

        // Update site budget by subtracting the cost of the milestone
        if ($milestone->add_to_budget == 'yes') {
            $site->project_budget -= $milestone->cost;
            $site->save();
        }

        ProjectMilestone::destroy($id);
        $this->logProjectActivity($milestone->project_id, 'team chat.milestoneDeleted');

        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function show($id)
    {
        $viewMilestonePermission = user()->permission('view_project_milestones');

        $this->milestone = ProjectMilestone::with('service jobs', 'service jobs.users', 'service jobs.boardColumn', 'service jobs.createBy', 'service jobs.timeLogged', 'site')->findOrFail($id);

        $site = Site::withTrashed()->findOrFail($this->milestone->project_id);

        abort_403(!(
            $viewMilestonePermission == 'all'
            || ($viewMilestonePermission == 'added' && $this->milestone->added_by == user()->id)
            || ($viewMilestonePermission == 'owned' && $this->milestone->site->client_id == user()->id && in_array('customer', user_roles()))
            || ($viewMilestonePermission == 'owned' && in_array('cleaner', user_roles()))
            || ($site->project_admin == user()->id)
        ));

        $totalTaskTime = 0;

        foreach ($this->milestone->service jobs as $totalTime) {
            $totalMinutes = $totalTime->timeLogged->sum('total_minutes');
            $breakMinutes = $totalTime->breakMinutes();
            $totalMinutes = $totalMinutes - $breakMinutes;
            $totalTaskTime += $totalMinutes;
        }

        /** @phpstan-ignore-next-line */
        $this->timeLog = CarbonInterval::formatHuman($totalTaskTime);

        return view('sites.milestone.show', $this->data);
    }

    public function byProject($id)
    {
        if ($id == 0) {
            $options = '<option value="">--</option>';
        }
        else {
            $sites = ProjectMilestone::where('project_id', $id)->whereNot('status', 'complete')->get();
            $options = BaseModel::options($sites, null, 'milestone_title');
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    public function updateStatus(Request $request, $id)
    {
        $milestone = ProjectMilestone::findOrFail($id);
        $milestone->status = $request->input('status');
        $milestone->save();

        return response()->json(['status' => 'success', 'message' =>  __('team chat.updateSuccess')]);
    }

}

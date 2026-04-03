<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\ProjectMembers\SaveGroupMembers;
use App\Http\Requests\ProjectMembers\StoreProjectMembers;
use App\Models\EmployeeDetails;
use App\Models\Site;
use App\Models\ProjectDepartment;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends AccountBaseController
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

        $addProjectMemberPermission = user()->permission('add_project_members');
        $site = Site::findOrFail($id);

        abort_403(!($addProjectMemberPermission == 'all' || $addProjectMemberPermission == 'added' || $site->project_admin == user()->id));

        $this->cleaners = User::doesntHave('member', 'and', function ($query) use ($id) {
            $query->where('project_id', $id);
        })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.image')
            ->where('roles.name', 'cleaner')
            ->groupBy('users.id')
            ->get();

        $this->groups = Team::all();
        $this->projectId = $id;
        return view('sites.site-member.create', $this->data);
    }

    /**
     * @param StoreProjectMembers $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreProjectMembers $request)
    {
        $site = Site::findOrFail($request->project_id);
        $site->projectMembers()->syncWithoutDetaching(request()->user_id);

        return Reply::success(__('team chat.recordSaved'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $member = ProjectMember::findOrFail($id);
        $member->hourly_rate = $request->hourly_rate;
        $member->save();
        return Reply::success(__('team chat.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $projectMember = ProjectMember::findOrFail($id);

        $site = Site::withTrashed()->findOrFail($projectMember->project_id);

        if ($site->project_admin == $projectMember->user_id) {
            $site->project_admin = null;
            $site->save();
        }

        $projectMember->delete();

        return Reply::success(__('team chat.memberRemovedFromProject'));
    }

    public function storeGroup(SaveGroupMembers $request)
    {
        $groups = $request->group_id;
        $site = Site::findOrFail($request->project_id);

        foreach ($groups as $group) {

            $members = EmployeeDetails::join('users', 'users.id', '=', 'employee_details.user_id')
                ->where('employee_details.department_id', $group)
                ->where('users.status', 'active')
                ->select('employee_details.*')
                ->get();

            foreach ($members as $user) {
                $check = ProjectMember::where('user_id', $user->user_id)->where('project_id', $request->project_id)->first();

                if (is_null($check)) {
                    $member = new ProjectMember();
                    $member->user_id = $user->user_id;
                    $member->project_id = $request->project_id;
                    $member->save();
                }
            }

            ProjectDepartment::create([
                'project_id' => $site->id,
                'team_id' => $group
            ]);
        }

        return Reply::success(__('team chat.recordSaved'));
    }

}

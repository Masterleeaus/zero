<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\LeaveType\StoreLeaveType;
use App\Models\BaseModel;
use App\Models\Role;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\Team;
use App\Models\Leave;

class LeaveTypeController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projectSettings';
        $this->activeSettingMenu = 'project_settings';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->teams = Team::all();
        $this->roles = Role::allDesignations();
        $this->roles = Role::where('name', '<>', 'customer')->get();

        return view('leave-settings.create-leave-setting-type-modal', $this->data);
    }

    /**
     * @param StoreLeaveType $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreLeaveType $request)
    {
        $leaveType = new LeaveType();
        $leaveType->type_name = $request->type_name;
        $leaveType->leavetype = $request->leavetype;
        $leaveType->color = $request->color;
        $leaveType->paid = $request->paid;

        if($request->leavetype == 'monthly'){
            $leaveType->no_of_leaves = $request->monthly_leave_number;
            $leaveType->monthly_limit = 0;

        }else{
            $leaveType->no_of_leaves = $request->yearly_leave_number;
            $leaveType->monthly_limit = $request->monthly_limit;
        }

        $leaveType->effective_after = $request->effective_after;
        $leaveType->effective_type = $request->effective_type;
        $leaveType->unused_leave = $request->unused_leave;
        $leaveType->over_utilization = $request->over_utilization;
        $leaveType->encashed = $request->has('encashed') ? 1 : 0;
        $leaveType->allowed_probation = $request->has('allowed_probation') ? 1 : 0;
        $leaveType->allowed_notice = $request->has('allowed_notice') ? 1 : 0;
        $leaveType->gender = $request->gender ? json_encode($request->gender) : null;
        $leaveType->marital_status = $request->marital_status ? json_encode($request->marital_status) : null;
        $leaveType->zone = $request->zone ? json_encode($request->zone) : null;
        $leaveType->role = $request->role ? json_encode($request->role) : null;
        $leaveType->role = $request->role ? json_encode($request->role) : null;
        $leaveType->save();

        $leaveTypes = LeaveType::get();

        $options = BaseModel::options($leaveTypes, $leaveType, 'type_name');

        return Reply::successWithData(__('team chat.leaveTypeAdded'), ['data' => $options, 'page_reload' => $request->page_reload]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->leaveType = LeaveType::findOrFail($id);
        $this->allTeams = Team::all();
        $this->allDesignations = Role::allDesignations();
        $this->allRoles = Role::where('name', '<>', 'customer')->get();
        $this->allGenders = ['male', 'female', 'others'];
        $this->gender = json_decode($this->leaveType->gender);
        $this->maritalStatus = json_decode($this->leaveType->marital_status);
        $this->zone = json_decode($this->leaveType->zone);
        $this->role = json_decode($this->leaveType->role);
        $this->role = json_decode($this->leaveType->role);

        return view('leave-settings.edit-leave-setting-type-modal', $this->data);
    }

    public function update(StoreLeaveType $request, $id)
    {

        if ($request->leaves < 0) {
            return Reply::error('team chat.leaveTypeValueError');
        }

        $leaveType = LeaveType::findOrFail($id);

        if ($request->paid !== (string)$leaveType->paid) {
            Leave::where('leave_type_id', $leaveType->id)->update(['paid' => $request->paid]);
        }

        $leaveType->type_name = $request->type_name;
        $leaveType->color = $request->color;
        $leaveType->paid = $request->paid;
        
        // need values later no of leaves early one
        session([
                'old_leaves' => $leaveType->no_of_leaves,
                'old_leavetype' => $leaveType->leavetype
            ]);

        if($leaveType->leavetype == 'monthly'){
            $leaveType->no_of_leaves = $request->monthly_leave_number;
            $leaveType->monthly_limit = 0;

        }else{
            $leaveType->no_of_leaves = $request->yearly_leave_number;
            $leaveType->monthly_limit = $request->monthly_limit;
        }

        $leaveType->monthly_limit = $request->monthly_limit;
        $leaveType->effective_after = $request->effective_after;
        $leaveType->effective_type = $request->effective_type;
        $leaveType->encashed = $request->encashed;
        $leaveType->allowed_probation = $request->allowed_probation;
        $leaveType->allowed_notice = $request->allowed_notice;
        $leaveType->gender = $request->gender ? json_encode($request->gender) : null;
        $leaveType->marital_status = $request->marital_status ? json_encode($request->marital_status) : null;
        $leaveType->zone = $request->zone ? json_encode($request->zone) : null;
        $leaveType->role = $request->role ? json_encode($request->role) : null;
        $leaveType->role = $request->role ? json_encode($request->role) : null;
        $leaveType->over_utilization = $request->over_utilization;
        $leaveType->save();

        return Reply::success(__('team chat.leaveTypeAdded'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $leaveType = LeaveType::withTrashed()->find($id);

        if (request()->has('restore') && request()->restore == 'restore') {
            if ($leaveType && $leaveType->trashed()) {
                $leaveType->restore();
                return Reply::success(__('team chat.restoreSuccess'));
            }
        }

        if (request()->has('archive') && request()->archive == 'archive') {
            if ($leaveType) {
                $leaveType->delete();
                return Reply::success(__('team chat.archiveSuccess'));
            }
        }

        if (request()->has('force_delete') && request()->force_delete == 'force_delete') {
            if ($leaveType) {
                $leaveType->forceDelete();
                return Reply::success(__('team chat.deleteSuccess'));
            }
        }

        LeaveType::destroy($id);
        return Reply::success(__('team chat.deleteSuccess'));
    }

}

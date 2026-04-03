<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Role;
use App\Models\EmployeeDetails;
use Illuminate\Http\Request;
use App\DataTables\DesignationDataTable;
use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\UpdateRequest;

class DesignationController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.role');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('cleaners', $this->user->modules));
            return $next($request);
        });
    }

    public function index(DesignationDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_designation');
        abort_403(!in_array($viewPermission, ['all']));

        // get all roles
        $this->roles = Role::all();
        return $dataTable->render('role.index', $this->data);
    }

    public function create()
    {
        $this->roles = Role::all();
        $this->view = 'role.ajax.create';

        if (request()->model == true) {
            return view('cleaners.create_designation', $this->data);
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('role.create', $this->data);
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        $group = new Role();
        $group->name = $request->name;
        $group->parent_id = $request->parent_id ? $request->parent_id : null;
        $group->save();

        $redirectUrl = urldecode($request->redirect_url);
        $this->roles = Role::all();

        if ($redirectUrl == '') {
            $redirectUrl = route('roles.index');
        }


        return Reply::successWithData(__('team chat.recordSaved'), ['roles' => $this->roles, 'redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $this->role = Role::findOrFail($id);
        $this->parent = Role::where('id', $this->role->parent_id)->first();

        $this->view = 'role.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('role.create', $this->data);
    }

    public function edit($id)
    {
        $this->role = Role::findOrFail($id);

        $roles = Role::where('id', '!=', $this->role->id)->get();

        $childDesignations = $roles->where('parent_id', $this->role->id)->pluck('id')->toArray();

        $roles = $roles->where('parent_id', '!=', $this->role->id);

        // remove child roles
        $this->roles = $roles->filter(function ($value, $key) use ($childDesignations) {
            return !in_array($value->parent_id, $childDesignations);
        });


        $this->view = 'role.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('role.create', $this->data);

    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */

    public function update(UpdateRequest $request, $id)
    {
        $editDesignation = user()->permission('edit_designation');
        abort_403($editDesignation != 'all');

        $group = Role::findOrFail($id);

        if($request->parent_id != null)
        {
            $parent = Role::findOrFail($request->parent_id);

            if($id == $parent->parent_id)
            {
                $parent->parent_id = $group->parent_id;
                $parent->save();
            }
        }

        $group->name = strip_tags($request->designation_name);
        $group->parent_id = $request->parent_id ? $request->parent_id : null;
        $group->save();

        $redirectUrl = route('roles.index');
        return Reply::successWithData(__('team chat.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_designation');
        abort_403($deletePermission != 'all');

        EmployeeDetails::where('designation_id', $id)->update(['designation_id' => null]);
        $role = Role::where('parent_id', $id)->get();
        $parent = Role::findOrFail($id);

        if(count($role) > 0)
        {
            foreach($role as $role)
            {
                $child = Role::findOrFail($role->id);
                $child->parent_id = $parent->parent_id;
                $child->save();
            }
        }

        Role::destroy($id);

        $redirectUrl = route('roles.index');
        return Reply::successWithData(__('team chat.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {

        if ($request->action_type === 'delete') {
            $this->deleteRecords($request);
            return Reply::success(__('team chat.deleteSuccess'));
        }

        return Reply::error(__('team chat.selectAction'));

    }

    protected function deleteRecords($request)
    {
        $deletePermission = user()->permission('delete_department');
        abort_403($deletePermission != 'all');

        $rowIds = explode(',', $request->row_ids);

        if (($key = array_search('on', $rowIds)) !== false) {
            unset($rowIds[$key]);
        }

        foreach ($rowIds as $id) {
            EmployeeDetails::where('designation_id', $id)->update(['designation_id' => null]);
            $role = Role::where('parent_id', $id)->get();
            $parent = Role::findOrFail($id);

            if(count($role) > 0)
            {
                foreach($role as $role)
                {
                    $child = Role::findOrFail($role->id);
                    $child->parent_id = $parent->parent_id;
                    $child->save();
                }
            }
        }

        Role::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    public function hierarchyData()
    {
        $viewPermission = user()->permission('view_designation');
        abort_403($viewPermission != 'all');

        $this->pageTitle = 'Role Hierarchy';
        $this->chartDesignations = Role::get(['id','name','parent_id']);
        $this->roles = Role::with('childs')->where('parent_id', null)->get();

        if(request()->ajax())
        {
            return Reply::dataOnly(['status' => 'success', 'roles' => $this->roles]);
        }

        return view('roles-hierarchy.index', $this->data);
    }

    public function changeParent()
    {
        $editPermission = user()->permission('edit_designation');
        abort_403($editPermission != 'all');

        $child_ids = request('values');
        $parent_id = request('newParent') ? request('newParent') : request('parent_id');

        $role = Role::findOrFail($parent_id);
        // Root node again
        if(request('newParent') && $role)
        {
            $role->parent_id = null;
            $role->save();
        }
        else if ($role && $child_ids != '') // update child Node
        {
            foreach ($child_ids as $child_id)
            {
                $child = Role::findOrFail($child_id);

                if ($child)
                {
                    $child->parent_id = $parent_id;
                    $child->save();
                }

            }
        }

        $this->chartDesignations = Role::get(['id','name','parent_id']);
        $this->roles = Role::with('childs')->where('parent_id', null)->get();

        $html = view('roles-hierarchy.chart_tree', $this->data)->render();
        $organizational = view('roles-hierarchy.chart_organization', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html,'organizational' => $organizational]);

    }

    public function searchFilter()
    {
        $text = request('searchText');

        if($text != '' && strlen($text) > 2)
        {
            $searchParent = Role::with('childs')->where('name', 'like', '%' . $text . '%')->get();

            $id = [];

            foreach($searchParent as $item)
            {
                array_push($id, $item->parent_id);
            }

            $item = $searchParent->whereIn('id', $id)->pluck('id');
            $this->chartDepartments = $searchParent;

            if($text != '' && !is_null($item)){
                foreach($this->chartDepartments as $item){
                    $item['parent_id'] = null;
                }
            }

            $parent = array();

            foreach($this->chartDepartments as $role)
            {
                array_push($parent, $role->id);

                if ($role->childs)
                {
                    $this->child($role->childs);
                }
            }

            $this->children = Role::whereIn('id', $this->arr)->get(['id','name','parent_id']);
            $this->parents = Role::whereIn('id', $parent)->get(['id','name']);
            $this->chartDesignations = $this->parents->merge($this->children);

            $this->roles = Role::with('childs')
                ->where('name', 'like', '%' . $text . '%')
                ->get();
        }
        else
        {
            $this->chartDesignations = Role::get(['id','name','parent_id']);
            $this->roles = Role::with('childs')->where('parent_id', null)->get();
        }

        $html = view('roles-hierarchy.chart_tree', $this->data)->render();
        $organizational = view('roles-hierarchy.chart_organization', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html,'organizational' => $organizational]);

    }

    public function child($child)
    {
        foreach($child as $item)
        {
            array_push($this->arr, $item->id);

            if ($item->childs)
            {
                $this->child($item->childs);
            }
        }


    }

}

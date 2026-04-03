<?php

namespace App\Http\Controllers;

use App\DataTables\DepartmentDataTable;
use App\Helper\Reply;
use App\Models\Team;
use App\Http\Requests\Team\StoreDepartment;
use App\Http\Requests\Team\UpdateDepartment;
use App\Models\EmployeeDetails;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.zone');

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('cleaners', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * @param DepartmentDataTable $dataTable
     * @return mixed|void
     */

    public function index(DepartmentDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_department');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->zones = Team::with('childs')->get();

        return $dataTable->render('zones.index', $this->data);
    }

    public function create()
    {
        $this->zones = Team::allDepartments();

        $this->view = 'zones.ajax.create';

        if (request()->model == true) {
            return view('cleaners.create_department', $this->data);
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('zones.create', $this->data);
    }

    /**
     * @param StoreDepartment $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreDepartment $request)
    {

        $group = new Team();
        $group->team_name = $request->team_name;
        $group->parent_id = $request->parent_id;
        $group->save();

        $this->zones = Team::allDepartments();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('zones.index');
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['zones' => $this->zones, 'redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $this->zone = Team::findOrFail($id);
        $this->parent = Team::where('id', $this->zone->parent_id)->first();


        $this->view = 'zones.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('zones.create', $this->data);
    }

    public function edit($id)
    {
        $this->zone = Team::findOrFail($id);
        $zones = Team::where('id', '!=', $this->zone->id)->get();

        $childDepartments = $zones->where('parent_id', $this->zone->id)->pluck('id')->toArray();

        $zones = $zones->where('parent_id', '!=', $this->zone->id);

        // remove child zones
        $this->zones = $zones->filter(function ($value, $key) use ($childDepartments) {
            return !in_array($value->parent_id, $childDepartments);
        });

        $this->view = 'zones.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('zones.create', $this->data);
    }

    /**
     * @param UpdateDepartment $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateDepartment $request, $id)
    {
        $editDepartment = user()->permission('edit_department');
        abort_403($editDepartment != 'all');

        $group = Team::findOrFail($id);
        $group->team_name = strip_tags($request->team_name);
        $group->parent_id = $request->parent_id ?? null;
        $group->save();

        $redirectUrl = route('zones.index');

        return Reply::successWithData(__('team chat.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_department');
        abort_403($deletePermission != 'all');

        EmployeeDetails::where('department_id', $id)->update(['department_id' => null]);
        $zone = Team::where('parent_id', $id)->get();
        $parent = Team::findOrFail($id);

        if (count($zone) > 0) {
            foreach ($zone as $item) {
                $child = Team::findOrFail($item->id);
                $child->parent_id = $parent->parent_id;
                $child->save();
            }
        }

        Team::destroy($id);

        $redirectUrl = route('zones.index');

        return Reply::successWithData(__('team chat.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            $this->deleteRecords($request);
            return Reply::success(__('team chat.deleteSuccess'));
        }

        return Reply::error(__('team chat.selectAction'));

    }

    protected function deleteRecords($request)
    {
        $deletePermission = user()->permission('delete_department');
        abort_403($deletePermission != 'all');

        $item = explode(',', $request->row_ids);

        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        foreach($item as $id)
        {
            EmployeeDetails::where('department_id', $id)->update(['department_id' => null]);
            $zone = Team::where('parent_id', $id)->get();
            $parent = Team::findOrFail( $id);

            if (count($zone) > 0) {
                foreach ($zone as $item) {
                    $child = Team::findOrFail($item->id);
                    $child->parent_id = $parent->parent_id;
                    $child->save();
                }
            }

            Team::where('id', $id)->delete();
        }

    }

    public function hierarchyData()
    {
        $viewPermission = user()->permission('view_department');
        abort_403($viewPermission != 'all');

        $this->editPermission = user()->permission('edit_department');
        $this->pageTitle = 'Zone Hierarchy';
        $this->chartDepartments = Team::get(['id', 'team_name', 'parent_id']);
        $this->zones = Team::with('childs', 'childs.childs')->where('parent_id', null)->get();

        if (request()->ajax()) {
            return Reply::dataOnly(['status' => 'success', 'zones' => $this->zones]);
        }

        return view('zones-hierarchy.index', $this->data);
    }

    public function changeParent()
    {
        $editPermission = user()->permission('edit_department');
        abort_403($editPermission != 'all');

        $childIds = request('values');
        $parentId = request('newParent') ? request('newParent') : request('parent_id');

        $zone = Team::findOrFail($parentId);

        // Root node again
        if (request('newParent') && $zone) {
            $zone->parent_id = null;
            $zone->save();
        }
        else if ($zone && !is_null($childIds)) // update child Node
        {
            foreach ($childIds as $childId) {
                $child = Team::findOrFail($childId);

                if ($child) {
                    $child->parent_id = $parentId;
                    $child->save();
                }

            }
        }

        $this->chartDepartments = Team::get(['id', 'team_name', 'parent_id']);
        $this->zones = Team::with('childs')->where('parent_id', null)->get();
        $html = view('zones-hierarchy.chart_tree', $this->data)->render();
        $organizational = view('zones-hierarchy.chart_organization', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'organizational' => $organizational]);
    }

    // Search filter start

    public function searchDepartment(Request $request)
    {
        $text = $request->searchText;

        if ($text != '' && strlen($text) > 2) {
            $searchParent = Team::with('childs')->where('team_name', 'like', '%' . $text . '%')->get();

            $id = [];

            foreach ($searchParent as $item) {
                array_push($id, $item->parent_id);
            }

            $item = $searchParent->whereIn('id', $id)->pluck('id');
            $this->chartDepartments = $searchParent;

            if ($text != '' && !is_null($item)) {
                foreach ($this->chartDepartments as $item) {
                    $item['parent_id'] = null;
                }
            }

            $parent = array();

            foreach ($this->chartDepartments as $zone) {
                array_push($parent, $zone->id);

                if ($zone->childs) {
                    $this->child($zone->childs);
                }
            }

            $this->children = Team::whereIn('id', $this->arr)->get(['id', 'team_name', 'parent_id']);
            $this->parents = Team::whereIn('id', $parent)->get(['id', 'team_name']);
            $this->chartDepartments = $this->parents->merge($this->children);
        }
        else {
            $this->chartDepartments = Team::get(['id', 'team_name', 'parent_id']);

        }

        $this->zones = ($text != '') ? Team::with('childs')->where('team_name', 'like', '%' . $text . '%')->get() : Team::with('childs')->where('parent_id', null)->get();
        $html = view('zones-hierarchy.chart_tree', $this->data)->render();
        $organizational = view('zones-hierarchy.chart_organization', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'organizational' => $organizational]);
    }

    public function child($child)
    {
        foreach ($child as $item) {
            array_push($this->arr, $item->id);

            if ($item->childs) {
                $this->child($item->childs);
            }
        }
    }

    // Search filter end

    public function getMembers($id)
    {

        $options = '';
        $userData = [];
        $userId = explode(',', request()->get('userId'));

        if ($id == 0) {
            $members = User::allEmployees(null,true);

            foreach ($members as $item) {
                $self_select = (user() && user()->id == $item->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';

                $options .= '<option  data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div> ' . $item->name . '</span>' . $self_select . '" value="' . $item->id . '"> ' . $item->name . '</option>';
            }
        }
        else {
            $members = collect([]);
            $departmentIds = explode(',', $id);

            foreach ($departmentIds as $departmentId) {
                $members = $members->concat(User::departmentUsers($departmentId));
            }

            foreach ($members as $item) {
                $selected = '';

                if (isset($userId)){
                    if (in_array($item->id, $userId)) {
                        $selected = 'selected';
                    }
                }

                $self_select = (user() && user()->id == $item->id) ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>' : '';

                $options .= '<option ' . $selected . ' data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div>  ' . $item->name . '</span>' . $self_select . '" value="' . $item->id . '"> ' . $item->name . ' </option>';
                $url = route('cleaners.show', [$item->id]);

                $userData[] = ['id' => $item->id, 'value' => $item->name, 'image' => $item->image_url, 'link' => $url];

            }
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'userData' => $userData]);
    }

}

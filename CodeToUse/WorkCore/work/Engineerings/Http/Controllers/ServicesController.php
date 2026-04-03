<?php

namespace Modules\Engineerings\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tax;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\UnitType;
use App\Models\BaseModel;
use Modules\Units\Entities\Unit;
use Modules\Engineerings\Entities\Meter;
use Modules\Engineerings\Entities\Services;
use App\Http\Controllers\AccountBaseController;
use Modules\Engineerings\Http\Requests\StoreMeter;
use Modules\Engineerings\Entities\ServicesCategory;
use Modules\Engineerings\Http\Requests\StoreServices;
use Modules\Engineerings\Entities\ServicesSubCategory;

class ServicesController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('engineerings', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {
        $this->permissions = user()->permission('view_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle     = __('modules.invoices.addService');
        $this->services      = Services::with('category', 'subCategory', 'unit')->get();

        if (request()->ajax()) {
            $html = view('engineerings::services.ajax.index', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'engineerings::services.ajax.index';
        return view('engineerings::services.create', $this->data);
    }

    public function create()
    {
        $this->permissions = user()->permission('add_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle     = __('modules.invoices.addService');
        $this->taxes         = Tax::all();
        $this->categories    = ServicesCategory::all();
        $this->subCategories = ServicesSubCategory::all();
        $this->unit_types    = UnitType::all();

        if (request()->ajax()) {
            $html = view('engineerings::services.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'engineerings::services.ajax.create';
        return view('engineerings::services.create', $this->data);
    }

    public function store(StoreServices $request)
    {
        $data['name']            = $request->name;
        $data['price']           = $request->price;
        $data['taxes']           = $request->tax ? json_encode($request->tax) : null;
        $data['unit_id']         = $request->unit_type;
        $data['allow_purchase']  = $request->purchase_allow == 'no';
        $data['category_id']     = ($request->category_id) ?: null;
        $data['sub_category_id'] = ($request->sub_category_id) ?: null;
        $data['description']     = trim_editor($request->description);
        $data['last_updated_by'] = user() ? user()->id : null;
        $data['added_by']        = user() ? user()->id : null;

        if ($request->hasFile('image')) {
            $data['image'] = Files::upload($request->image, 'services-files', 300);
        }
        $services = Services::create($data);

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('services.create');
        }

        $allServices = Services::get();
        $options = BaseModel::options($allServices, null, 'name');

        $html = $this->create();
        return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true, 'itemID' => $services->id, 'data' => $options]);
    }

    public function destroy($id)
    {
        $this->permissions = user()->permission('delete_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $project = Services::findOrFail($id);
        Files::deleteDirectory(Services::FILE_PATH . '/' . $id);
        $project->forceDelete();
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function edit($id)
    {
        $this->permissions = user()->permission('edit_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle     = __('modules.invoices.editService');
        $this->taxes         = Tax::all();
        $this->categories    = ServicesCategory::all();
        $this->subCategories = ServicesSubCategory::all();
        $this->unit_types    = UnitType::all();
        $this->services      = Services::findOrFail($id);

        if (request()->ajax()) {
            $html = view('engineerings::services.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'engineerings::services.ajax.edit';
        return view('engineerings::services.create', $this->data);
    }

    public function update(StoreServices $request, $id)
    {
        $services                = Services::findOrFail($id);
        $data['name']            = $request->name;
        $data['price']           = $request->price;
        $data['taxes']           = $request->tax ? json_encode($request->tax) : null;
        $data['unit_id']         = $request->unit_type;
        $data['allow_purchase']  = $request->purchase_allow == 'no';
        $data['category_id']     = ($request->category_id) ?: null;
        $data['sub_category_id'] = ($request->sub_category_id) ?: null;
        $data['description']     = trim_editor($request->description);
        $data['last_updated_by'] = user() ? user()->id : null;

        if ($request->hasFile('image')) {
            $data['image'] = Files::upload($request->image, 'services-files', 300);
        }

        $services->update($data);

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('services.index');
        }

        $allServices = Services::get();
        $options = BaseModel::options($allServices, null, 'name');

        $this->services      = Services::with('category', 'subCategory', 'unit')->get();

        $html = view('engineerings::services.ajax.index', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'add_more' => true, 'title' => __('modules.invoices.addService'), 'data' => $options]);
    }
}

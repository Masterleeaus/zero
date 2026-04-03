<?php

namespace Modules\Suppliers\Http\Controllers;

use App\Helper\Reply;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use Modules\Suppliers\Entities\Supplier;
use Modules\Suppliers\DataTables\SupplierDataTable;
use App\Http\Controllers\AccountBaseController;
use Modules\Suppliers\Http\Requests\StoreSupplier;
use Modules\Suppliers\Http\Requests\UpdateSupplier;

class SupplierController extends AccountBaseController

{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'suppliers::app.menu.supplier';
        $this->pageIcon  = 'ti-settings';
    }
      /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(SupplierDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_suppliers');
        abort_403(!in_array($viewPermission, ['all']));

        $this->suppliers      = Supplier::all();
        $this->totalUnits = count($this->suppliers);

        return $dataTable->render('suppliers::suppliers.index', $this->data);
    }

      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $viewPermission = user()->permission('add_suppliers');
        abort_403(!in_array($viewPermission, ['all']));

        $this->pageTitle = __('suppliers::app.menu.suppliers');

        if (request()->ajax()) {
            $html = view('suppliers::suppliers.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'suppliers::suppliers.ajax.create';
        return view('suppliers::suppliers.create', $this->data);
    }

      /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreSupplier $request)
    {
        $supplier                       = new Supplier();
        $supplier->name                 = $request->input('name');
        $supplier->alamat               = $request->input('alamat');
        $supplier->kode_pos             = $request->input('kode_pos');
        $supplier->phone                = $request->input('phone');
        $supplier->fax                  = $request->input('fax');
        $supplier->contact_person       = $request->input('contact_person');
        $supplier->phone_contact_person = $request->input('phone_contact_person');
        $supplier->email                = $request->input('email');
        $supplier->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('suppliers.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

      /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_suppliers');
        abort_403(!in_array($viewPermission, ['all']));

        $this->pageTitle = __('suppliers::app.menu.suppliers');

        $this->unit = Supplier::findOrFail($id);

        if (request()->ajax()) {
            $html = view('suppliers::suppliers.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'suppliers::suppliers.ajax.show';
        return view('suppliers::suppliers.create', $this->data);
    }

      /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewPermission = user()->permission('edit_suppliers');
        abort_403(!in_array($viewPermission, ['all']));

        $this->pageTitle = __('suppliers::app.menu.suppliers');
        $this->unit = Supplier::findOrFail($id);

        if (request()->ajax()) {
            $html = view('suppliers::suppliers.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'suppliers::suppliers.ajax.edit';
        return view('suppliers::suppliers.create', $this->data);
    }

      /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(UpdateSupplier $request, $id)
    {
        $editUnit = user()->permission('edit_suppliers');
        abort_403($editUnit != 'all');

        $supplier = Supplier::find($id);
        $supplier->name                 = $request->input('name');
        $supplier->alamat               = $request->input('alamat');
        $supplier->kode_pos             = $request->input('kode_pos');
        $supplier->phone                = $request->input('phone');
        $supplier->fax                  = $request->input('fax');
        $supplier->contact_person       = $request->input('contact_person');
        $supplier->phone_contact_person = $request->input('phone_contact_person');
        $supplier->email                = $request->input('email');
        $supplier->save();

        $redirectUrl = route('suppliers.index');
        return Reply::successWithData(__('messages.recordUpdate'), ['redirectUrl' => $redirectUrl]);
    }

      /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_suppliers');
        abort_403($deletePermission != 'all');

        Supplier::destroy($id);

        $redirectUrl = route('suppliers.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }
}

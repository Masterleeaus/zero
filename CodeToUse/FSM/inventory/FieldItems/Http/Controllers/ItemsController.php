<?php

namespace Modules\FieldItems\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\FieldItems\DataTables\ItemsDataTable;
use App\Http\Controllers\AccountBaseController;
use Modules\FieldItems\Entities\Item;
use Modules\FieldItems\Entities\ItemCategory;
use Modules\FieldItems\Entities\ItemFiles;
use Modules\FieldItems\Entities\ItemSubCategory;
use App\Models\Tax;
use App\Models\UnitType;
use App\Helper\Reply;
use Modules\FieldItems\Http\Requests\Item\StoreItemRequest;
use Modules\FieldItems\Http\Requests\Item\UpdateItemRequest;
use App\Helper\Files;

class ItemsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'fielditems::app.menu.items';
        $this->middleware(function ($request, $next) {
            in_array('client', user_roles()) ? abort_403(!in_array('items', $this->user->modules)) : abort_403(!in_array('items', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     * @param ItemsDataTable $dataTable
     * @return Renderable
     */
    public function index(ItemsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_item');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $itemDetails = [];

        if (request()->hasCookie('itemDetails')) {
            $itemDetails = json_decode(request()->cookie('itemDetails'), true);
        }

        $this->itemDetails = $itemDetails;

        $this->totalItems = Item::count();
        $this->categories = ItemCategory::all();
        $this->subCategories = ItemSubCategory::all();

        return $dataTable->render('fielditems::items.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $this->pageTitle = __('app.add') . ' ' . __('fielditems::app.menu.items');

        $this->addPermission = user()->permission('add_item');
        abort_403(!in_array($this->addPermission, ['all', 'added']));
        $this->taxes = Tax::all();
        $this->categories = ItemCategory::all();
        $this->subCategories = ItemSubCategory::all();

        $item = new Item();

        $this->unit_types = UnitType::all();

        if (!empty($item->getCustomFieldGroupsWithFields())) {
            $this->fields = $item->getCustomFieldGroupsWithFields()->fields;
        }


        if (request()->ajax()) {
            $html = view('fielditems::items.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'fielditems::items.ajax.create';

        return view('fielditems::items.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreItemRequest $request)
    {
        $this->addPermission = user()->permission('add_item');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $item = new Item();
        $item->name = $request->name;
        $item->price = $request->price;
        $item->qty_minimal = $request->qty_minimal;
        $item->taxes = $request->tax ? json_encode($request->tax) : null;
        $item->description = trim_editor($request->description);
        $item->hsn_sac_code = $request->hsn_sac_code;
        $item->unit_id = $request->unit_type;
        $item->allow_purchase = $request->purchase_allow == 'no';
        $item->downloadable = $request->downloadable == 'true';
        $item->category_id = ($request->category_id) ?: null;
        $item->sub_category_id = ($request->sub_category_id) ?: null;

        if (request()->hasFile('downloadable_file') && request()->downloadable == 'true') {
            Files::deleteFile($item->downloadable_file, ItemFiles::FILE_PATH);
            $item->downloadable_file = Files::uploadLocalOrS3(request()->downloadable_file, ItemFiles::FILE_PATH);
        }

        $item->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $item->updateCustomFieldData($request->custom_fields_data);
        }


        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('items.index');
        }

        if($request->add_more == 'true')
        {
            $html = $this->create();

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true, 'itemID' => $item->id, 'defaultImage' => $request->default_image ?? 0]);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl, 'itemID' => $item->id, 'defaultImage' => $request->default_image ?? 0]);

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $this->item = Item::with('category', 'subCategory')->findOrFail($id)->withCustomFields();
        $this->viewPermission = user()->permission('view_item');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $this->item->added_by == user()->id)));

        $this->taxes = Tax::withTrashed()->get();
        $this->pageTitle = $this->item->name;

        $this->taxValue = '';
        $taxes = [];

        foreach ($this->taxes as $tax) {
            if ($this->item && isset($this->item->taxes) && array_search($tax->id, json_decode($this->item->taxes)) !== false) {
                $taxes[] = $tax->tax_name . ' : ' . $tax->rate_percent . '%';
            }
        }

        $this->taxValue = implode(', ', $taxes);

        if (!empty($this->item->getCustomFieldGroupsWithFields())) {
            $this->fields = $this->item->getCustomFieldGroupsWithFields()->fields;
        }

        if (request()->ajax()) {
            $html = view('fielditems::items.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'fielditems::items.ajax.show';

        return view('fielditems::items.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $this->item = Item::findOrFail($id)->withCustomFields();

        $this->editPermission = user()->permission('edit_item');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->item->added_by == user()->id)));

        $this->taxes = Tax::all();
        $this->categories = ItemCategory::all();
        $this->unit_types = UnitType::all();
        $this->subCategories = !is_null($this->item->sub_category_id) ? ItemSubCategory::where('category_id', $this->item->category_id)->get() : [];
        $this->pageTitle = __('app.update') . ' ' . __('app.menu.items');


        $images = [];

        if (isset($this->item) && isset($this->item->files)) {
            foreach ($this->item->files as $file) {
                $image['id'] = $file->id;
                $image['name'] = $file->filename;
                $image['hashname'] = $file->hashname;
                $image['size'] = $file->size;
                $images[] = $image;
            }
        }

        $this->images = json_encode($images);

        if (!empty($this->item->getCustomFieldGroupsWithFields())) {
            $this->fields = $this->item->getCustomFieldGroupsWithFields()->fields;
        }

        if (request()->ajax()) {
            $html = view('fielditems::items.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'fielditems::items.ajax.edit';

        return view('fielditems::items.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(UpdateItemRequest $request, $id)
    {
        $item = Item::findOrFail($id)->withCustomFields();
        $this->editPermission = user()->permission('edit_item');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $item->added_by == user()->id)));

        $item->name = $request->name;
        $item->price = $request->price;
        $item->qty_minimal = $request->qty_minimal;
        $item->taxes = $request->tax ? json_encode($request->tax) : null;
        $item->hsn_sac_code = $request->hsn_sac_code;
        $item->unit_id = $request->unit_type;
        $item->description = trim_editor($request->description);
        $item->allow_purchase = ($request->purchase_allow == 'no') ? true : false;
        $item->downloadable = ($request->downloadable == 'true') ? true : false;
        $item->category_id = ($request->category_id) ? $request->category_id : null;
        $item->sub_category_id = ($request->sub_category_id) ? $request->sub_category_id : null;

        if (request()->hasFile('downloadable_file') && request()->downloadable == 'true') {
            Files::deleteFile($item->downloadable_file, ItemFiles::FILE_PATH);
            $item->downloadable_file = Files::uploadLocalOrS3(request()->downloadable_file, ItemFiles::FILE_PATH);
        }
        elseif (request()->downloadable == 'true' && $item->downloadable_file == null) {
            $item->downloadable = false;
        }

        // change default image
        if (!request()->hasFile('file')) {
            $item->default_image = request()->default_image;
        }

        $item->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $item->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('items.index'), 'defaultImage' => $request->default_image ?? 0]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $items = Item::findOrFail($id);
        $this->deletePermission = user()->permission('delete_item');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $items->added_by == user()->id)));

        $items->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('items.index')]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-purchase':
            $this->allowPurchase($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_item') != 'all');

        Item::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

    protected function allowPurchase($request)
    {
        abort_403(user()->permission('edit_item') != 'all');

        Item::whereIn('id', explode(',', $request->row_ids))->update(['allow_purchase' => $request->status]);
    }
}

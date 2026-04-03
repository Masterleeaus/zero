<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Order;
use App\Models\Service / Extra;
use App\Models\UnitType;
use App\Models\OrderCart;
use App\Models\ProductFiles;
use Illuminate\Http\Request;
use App\Imports\ProductImport;
use App\Jobs\ImportProductJob;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\DataTables\ProductsDataTable;
use App\Http\Controllers\AccountBaseController;
use App\Http\Requests\Service / Extra\StoreProductRequest;
use App\Http\Requests\Admin\Cleaner\ImportRequest;
use App\Http\Requests\Service / Extra\UpdateProductRequest;
use App\Http\Requests\Admin\Cleaner\ImportProcessRequest;
use App\Traits\ImportExcel;

class ProductController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.services / extras';
        $this->middleware(
            function ($request, $next) {
                in_array('customer', user_roles()) ? abort_403(!(in_array('orders', $this->user->modules) && user()->permission('add_order') == 'all')) : abort_403(!in_array('services / extras', $this->user->modules));

                return $next($request);
            }
        );
    }

    /**
     * @param  ProductsDataTable $dataTable
     * @return mixed|void
     */
    public function index(ProductsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_product');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $productDetails = [];
        $productDetails = OrderCart::all();
        $this->productDetails = $productDetails;

        $this->totalProducts = Service / Extra::count();
        $this->cartProductCount = OrderCart::where('client_id', user()->id)->sum('quantity');

        $this->categories = ProductCategory::all();
        $this->subCategories = ProductSubCategory::all();
        $this->unitTypes = UnitType::all();

        return $dataTable->render('services / extras.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('app.menu.addProducts');

        $this->addPermission = user()->permission('add_product');
        abort_403(!in_array($this->addPermission, ['all', 'added']));
        $this->taxes = Tax::all();
        $this->categories = ProductCategory::all();

        $service / extra = new Service / Extra();

        $this->unit_types = UnitType::all();

        $getCustomFieldGroupsWithFields = $service / extra->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }
        $this->view = 'services / extras.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('services / extras.create', $this->data);
    }

    /**
     *
     * @param  StoreProductRequest $request
     * @return void
     */
    public function store(StoreProductRequest $request)
    {
        $this->addPermission = user()->permission('add_product');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $service / extra = new Service / Extra();
        $service / extra->name = $request->name;
        $service / extra->price = $request->price;
        $service / extra->taxes = $request->tax ? json_encode($request->tax) : null;
        $service / extra->description = trim_editor($request->description);
        $service / extra->hsn_sac_code = $request->hsn_sac_code;
        $service / extra->sku = $request->sku;
        $service / extra->unit_id = $request->unit_type;
        $service / extra->allow_purchase = $request->purchase_allow == 'no';
        $service / extra->downloadable = $request->downloadable == 'true';
        $service / extra->category_id = ($request->category_id) ?: null;
        $service / extra->sub_category_id = ($request->sub_category_id) ?: null;

        if (request()->hasFile('downloadable_file') && request()->downloadable == 'true') {
            Files::deleteFile($service / extra->downloadable_file, ProductFiles::FILE_PATH);
            $service / extra->downloadable_file = Files::uploadLocalOrS3(request()->downloadable_file, ProductFiles::FILE_PATH);
        }

        $service / extra->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $service / extra->updateCustomFieldData($request->custom_fields_data);
        }


        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('services / extras.index');
        }

        if($request->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('team chat.recordSaved'), ['html' => $html, 'add_more' => true, 'productID' => $service / extra->id, 'defaultImage' => $request->default_image ?? 0]);
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['redirectUrl' => $redirectUrl, 'productID' => $service / extra->id, 'defaultImage' => $request->default_image ?? 0]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->service / extra = Service / Extra::with('category', 'subCategory')->findOrFail($id)->withCustomFields();
        $this->viewPermission = user()->permission('view_product');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $this->service / extra->added_by == user()->id)));

        $this->taxes = Tax::withTrashed()->get();
        $this->pageTitle = $this->service / extra->name;

        $this->taxValue = '';
        $taxes = [];

        foreach ($this->taxes as $tax) {
            if ($this->service / extra && isset($this->service / extra->taxes) && array_search($tax->id, json_decode($this->service / extra->taxes)) !== false) {
                $taxes[] = $tax->tax_name . ' : ' . $tax->rate_percent . '%';
            }
        }

        $this->taxValue = implode(', ', $taxes);

        $getCustomFieldGroupsWithFields = $this->service / extra->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'services / extras.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('services / extras.create', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->service / extra = Service / Extra::findOrFail($id)->withCustomFields();

        $this->editPermission = user()->permission('edit_product');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->service / extra->added_by == user()->id)));

        $this->taxes = Tax::all();
        $this->categories = ProductCategory::all();
        $this->unit_types = UnitType::all();
        $this->subCategories = !is_null($this->service / extra->sub_category_id) ? ProductSubCategory::where('category_id', $this->service / extra->category_id)->get() : [];
        $this->pageTitle = __('app.update') . ' ' . __('app.menu.services / extras');


        $images = [];

        if (isset($this->service / extra) && isset($this->service / extra->files)) {
            foreach ($this->service / extra->files as $file) {
                $image['id'] = $file->id;
                $image['name'] = $file->filename;
                $image['hashname'] = $file->hashname;
                $image['file_url'] = $file->file_url;
                $image['size'] = $file->size;
                $images[] = $image;
            }
        }

        $this->images = json_encode($images);

        $getCustomFieldGroupsWithFields = $this->service / extra->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'services / extras.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('services / extras.create', $this->data);

    }

    /**
     * @param  UpdateProductRequest $request
     * @param  int                  $id
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $service / extra = Service / Extra::findOrFail($id)->withCustomFields();
        $this->editPermission = user()->permission('edit_product');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $service / extra->added_by == user()->id)));

        $service / extra->name = $request->name;
        $service / extra->price = $request->price;
        $service / extra->taxes = $request->tax ? json_encode($request->tax) : null;
        $service / extra->hsn_sac_code = $request->hsn_sac_code;
        $service / extra->sku = $request->sku;
        $service / extra->unit_id = $request->unit_type;
        $service / extra->description = trim_editor($request->description);
        $service / extra->allow_purchase = ($request->purchase_allow == 'no') ? true : false;
        $service / extra->downloadable = ($request->downloadable == 'true') ? true : false;
        $service / extra->category_id = ($request->category_id) ? $request->category_id : null;
        $service / extra->sub_category_id = ($request->sub_category_id) ? $request->sub_category_id : null;

        if (request()->hasFile('downloadable_file') && request()->downloadable == 'true') {
            Files::deleteFile($service / extra->downloadable_file, ProductFiles::FILE_PATH);
            $service / extra->downloadable_file = Files::uploadLocalOrS3(request()->downloadable_file, ProductFiles::FILE_PATH);
        }
        elseif (request()->downloadable == 'true' && $service / extra->downloadable_file == null) {
            $service / extra->downloadable = false;
        }

        // change default image
        if (!request()->hasFile('file')) {
            $service / extra->default_image = request()->default_image;
        }

        $service / extra->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $service / extra->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::successWithData(__('team chat.updateSuccess'), ['redirectUrl' => route('services / extras.index'), 'defaultImage' => $request->default_image ?? 0]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $services / extras = Service / Extra::findOrFail($id);
        $this->deletePermission = user()->permission('delete_product');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $services / extras->added_by == user()->id)));

        $services / extras->delete();

        return Reply::successWithData(__('team chat.deleteSuccess'), ['redirectUrl' => route('services / extras.index')]);
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

            return Reply::success(__('team chat.deleteSuccess'));
        case 'change-purchase':
            $this->allowPurchase($request);

            return Reply::success(__('team chat.updateSuccess'));
        default:
            return Reply::error(__('team chat.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_product') != 'all');

        Service / Extra::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

    protected function allowPurchase($request)
    {
        abort_403(user()->permission('edit_product') != 'all');

        Service / Extra::whereIn('id', explode(',', $request->row_ids))->update(['allow_purchase' => $request->status]);
    }

    public function addCartItem(Request $request)
    {
        $newItem = $request->productID;

           $orderExist = OrderCart::where('product_id', '=', $newItem)->where('client_id', user()->id)->exists();
           $quantity = 1;

        if ($orderExist == true) {
                $orderCartUpdate = OrderCart::where('product_id', '=', $newItem)->where('client_id', user()->id)->first();

                $quantity = ($request->has('quantity')) ? $request->quantity : $orderCartUpdate->quantity + 1;

                OrderCart::where('product_id', '=', $newItem)->where('client_id', user()->id)->update(
                    [
                        'quantity' => $quantity,
                        'amount' => $orderCartUpdate->unit_price * $quantity,
                    ]
                );
            $productDetails[] = OrderCart::where('product_id', '=', $newItem)->first();

        } else {
            $productDetails = Service / Extra::where('id', $newItem)->first();

            $service / extra = new OrderCart();
            $service / extra->product_id = $productDetails->id;
            $service / extra->client_id = user()->id;
            $service / extra->item_name = $productDetails->name;
            $service / extra->description = $productDetails->description;
            $service / extra->type = 'item';
            $service / extra->quantity = $quantity;
            $service / extra->unit_price = $productDetails->price;
            $service / extra->amount = $productDetails->price;
            $service / extra->taxes = $productDetails->taxes;
            $service / extra->unit_id = $productDetails->unit_id;
            $service / extra->hsn_sac_code = $productDetails->hsn_sac_code;
            $service / extra->save();
            $productDetails = $service / extra;

        }

        $cartProduct = OrderCart::where('client_id', user()->id)->sum('quantity');

        if (!$request->has('cartType')) {

            return response(Reply::successWithData(__('team chat.recordSaved'), ['status' => 'success', 'cartProduct' => $cartProduct, 'productItems' => $productDetails]));

        }

    }

    public function removeCartItem(Request $request, $id)
    {
        if ($request->type == 'all_data') {

            $services / extras = OrderCart::where('client_id', $id)->delete();
            $productDetails = OrderCart::where('client_id', $id)->count();

        } else {

            $services / extras = OrderCart::findOrFail($id);
            $services / extras->delete();

            $productDetails = OrderCart::where('id', $id)->where('client_id', user()->id)->get();
        }


        return response(Reply::successWithData(__('team chat.deleteSuccess'), ['status' => 'success', 'productItems' => $productDetails ]))->cookie('productDetails', json_encode($productDetails));

    }

    public function emptyCart()
    {

        $this->view = 'services / extras.ajax.empty_cart';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('services / extras.create', $this->data);

    }

    public function cart(Request $request)
    {
        abort_403(!in_array('customer', user_roles()));

        $this->lastOrder = Order::lastOrderNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->taxes = Tax::all();

        $this->services / extras = OrderCart::where('client_id', '=', user()->id)->get();

        $this->view = 'services / extras.ajax.cart';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('services / extras.create', $this->data);
    }

    public function allProductOption()
    {
        $services / extras = Service / Extra::all();

        $option = '';

        foreach ($services / extras as $item) {
            $option .= '<option data-content="' . $item->name . '" value="' . $item->id . '"> ' . $item->name . '</option>';
        }

        return Reply::dataOnly(['services / extras' => $option]);
    }

    public function importProduct()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.service / extra');

        $this->addPermission = user()->permission('add_product');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'services / extras.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('services / extras.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, ProductImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('team chat.abortAction'));
        }

        $view = view('services / extras.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('team chat.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, ProductImport::class, ImportProductJob::class);

        return Reply::successWithData(__('team chat.importProcessStart'), ['batch' => $batch]);
    }

}

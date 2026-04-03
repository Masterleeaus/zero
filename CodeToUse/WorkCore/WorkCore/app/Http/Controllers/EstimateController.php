<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Tax;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Invoice;
use App\Models\Service / Extra;
use App\Models\Currency;
use App\Models\Quote;
use App\Models\UnitType;
use App\Models\EstimateItem;
use App\Models\InvoiceItems;
use Illuminate\Http\Request;
use App\Models\AcceptEstimate;
use App\Models\ProductCategory;
use App\Events\NewEstimateEvent;
use App\Models\EstimateTemplate;
use App\Models\EstimateItemImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Requests\StoreEstimate;
use App\Models\EstimateTemplateItem;
use Illuminate\Support\Facades\File;
use App\DataTables\EstimatesDataTable;
use App\Http\Requests\EstimateAcceptRequest;
use App\Models\EstimateRequest;
use App\Helper\UserService;
use App\Models\Site;

class EstimateController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.quotes';
        $this->pageIcon = 'ti-file';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('quotes', $this->user->modules));

            return $next($request);
        });
    }

    public function index(EstimatesDataTable $dataTable)
    {
        abort_403(!in_array(user()->permission('view_estimates'), ['all', 'added', 'owned', 'both']));

        return $dataTable->render('quotes.index', $this->data);

    }

    public function create()
    {
        $this->addPermission = user()->permission('add_estimates');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if (request('quote') != '') {
            $this->estimateId = request('quote');
            $this->type = 'quote';
            $this->quote = Quote::with('items', 'items.estimateItemImage', 'customer', 'unit', 'customer.sites')->findOrFail($this->estimateId);
        }

        $this->pageTitle = __('modules.quotes.createEstimate');
        $this->customers = User::allClients();
        $this->currencies = Currency::all();
        $this->lastEstimate = Quote::lastEstimateNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->zero = '';

        if (strlen($this->lastEstimate) < $this->invoiceSetting->estimate_digit) {
            $condition = $this->invoiceSetting->estimate_digit - strlen($this->lastEstimate);

            for ($i = 0; $i < $condition; $i++) {
                $this->zero = '0' . $this->zero;
            }
        }

        $this->taxes = Tax::all();
        $this->services / extras = Service / Extra::all();
        $this->categories = ProductCategory::all();
        $this->template = EstimateTemplate::all();
        $this->units = UnitType::all();

        $this->estimateTemplate = null;
        $this->estimateTemplateItem = null;

        if (request()->has('template')) {
            $this->estimateTemplate = EstimateTemplate::findOrFail(request('template'));

            $this->estimateTemplateItem = EstimateTemplateItem::with('estimateTemplateItemImage')->where('estimate_template_id', request('template'))->get();
        }

        if (request()->has('quote-request')) {
            $this->estimateTemplate = EstimateRequest::findOrFail(request('quote-request'));
            $this->estimateRequestId = $this->estimateTemplate->id;
        }

        // this data is sent from site and customer invoices
        $this->site = request('project_id') ? Site::findOrFail(request('project_id')) : null;

        $quote = new Quote();
        $getCustomFieldGroupsWithFields = $quote->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->customer = isset(request()->default_client) ? User::findOrFail(request()->default_client) : null;

        $userId = UserService::getUserId();
        $this->isClient = User::isClient($userId);

        if ($this->isClient) {
            $this->customer = User::with('sites')->findOrFail($userId);
        }

        if (request()->has('quote-request')) {
            $this->customer = EstimateRequest::findOrFail(request('quote-request'))->customer;
        }

        $this->view = 'quotes.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('quotes.create', $this->data);

    }

    public function store(StoreEstimate $request)
    {
        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

        if (trim($items[0]) == '' || trim($cost_per_item[0]) == '') {
            return Reply::error(__('team chat.addItem'));
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && (intval($qty) < 1)) {
                return Reply::error(__('team chat.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('team chat.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('team chat.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('team chat.itemBlank'));
            }
        }

        $quote = new Quote();
        $quote->client_id = $request->client_id;
        $quote->project_id = $request->project_id ?? null;
        $quote->valid_till = companyToYmd($request->valid_till);
        $quote->sub_total = round($request->sub_total, 2);
        $quote->total = round($request->total, 2);
        $quote->currency_id = $request->currency_id;
        $quote->note = trim_editor($request->note);
        $quote->discount = round($request->discount_value, 2);
        $quote->discount_type = $request->discount_type;
        $quote->status = 'waiting';
        $quote->description = trim_editor($request->description);
        $quote->estimate_number = $request->estimate_number;
        $quote->estimate_request_id = $request->estimate_request_id ?? null;
        $quote->save();

        if (request()->has('estimate_request_id')) {
            $estimateRequest = EstimateRequest::findOrFail(request('estimate_request_id'));
            $estimateRequest->update(['status' => 'accepted', 'estimate_id' => $quote->id]);
        }


        // To add custom fields data
        if ($request->custom_fields_data) {
            $quote->updateCustomFieldData($request->custom_fields_data);
        }

        $this->logSearchEntry($quote->id, $quote->estimate_number, 'quotes.show', 'quote');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('quotes.index');
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['estimateId' => $quote->id, 'redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $this->invoice = Quote::with('sign', 'customer', 'unit', 'clientdetails')->findOrFail($id)->withCustomFields();
        $this->viewPermission = user()->permission('view_estimates');
        $userId = UserService::getUserId();

        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->invoice->added_by == $userId)
            || ($this->viewPermission == 'owned' && $this->invoice->client_id == $userId)
            || ($this->viewPermission == 'both' && ($this->invoice->client_id == $userId || $this->invoice->added_by == $userId))
        ));

        if($this->invoice->discount_type == 'percent') {
            $discountAmount = $this->invoice->discount;
            $this->discountType = $discountAmount.'%';
        }else {
            $discountAmount = $this->invoice->discount;
            $this->discountType = currency_format($discountAmount, $this->invoice->currency_id);
        }

        $getCustomFieldGroupsWithFields = $this->invoice->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = $this->invoice->estimate_number;

        $this->discount = 0;

        if ($this->invoice->discount > 0) {
            if ($this->invoice->discount_type == 'percent') {
                $this->discount = (($this->invoice->discount / 100) * $this->invoice->sub_total);
            }
            else {
                $this->discount = $this->invoice->discount;
            }
        }

        $taxList = array();

        $this->firstEstimate = Quote::orderBy('id', 'desc')->first();

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {

                        if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = company();
        $this->invoiceSetting = invoice_setting();

        if (in_array('customer', user_roles())) {
            $lastViewed = now();
            $ipAddress = request()->ip();
            $this->invoice->last_viewed = $lastViewed;
            $this->invoice->ip_address = $ipAddress;
            $this->invoice->save();
        }

        return view('quotes.show', $this->data);

    }

    public function edit($id)
    {
        $this->quote = Quote::with('items.estimateItemImage')->findOrFail($id)->withCustomFields();
        $userId = UserService::getUserId();
        $this->editPermission = user()->permission('edit_estimates');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && ($this->quote->added_by == user()->id || $this->quote->added_by == $userId))
            || ($this->editPermission == 'owned' && $this->quote->client_id == $userId)
            || ($this->editPermission == 'both' && ($this->quote->client_id == $userId || $this->quote->added_by == user()->id || $this->quote->added_by == $userId))
        ));

        $this->pageTitle = $this->quote->estimate_number;

        $getCustomFieldGroupsWithFields = $this->quote->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->isClient = User::isClient($userId);

        $this->units = UnitType::all();
        $this->customers = User::allClients();
        $this->currencies = Currency::all();
        $this->taxes = Tax::all();
        $this->services / extras = Service / Extra::all();
        $this->categories = ProductCategory::all();
        $this->invoiceSetting = invoice_setting();

        $this->view = 'quotes.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('quotes.create', $this->data);
    }

    public function update(StoreEstimate $request, $id)
    {
        $items = $request->item_name;
        $itemsSummary = $request->item_summary;
        $hsn_sac_code = $request->hsn_sac_code;
        $unitId = request()->unit_id;
        $service / extra = request()->product_id;
        $tax = $request->taxes;
        $quantity = $request->quantity;
        $cost_per_item = $request->cost_per_item;
        $amount = $request->amount;
        $invoice_item_image = $request->invoice_item_image;
        $invoice_item_image_url = $request->invoice_item_image_url;
        $item_ids = $request->item_ids;

        if (trim($items[0]) == '' || trim($items[0]) == '' || trim($cost_per_item[0]) == '') {
            return Reply::error(__('team chat.addItem'));
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && $qty < 1) {
                return Reply::error(__('team chat.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('team chat.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('team chat.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('team chat.itemBlank'));
            }
        }

        $quote = Quote::findOrFail($id);
        $quote->client_id = $request->client_id;
        $quote->project_id = $request->project_id ?? null;
        $quote->valid_till = companyToYmd($request->valid_till);
        $quote->sub_total = round($request->sub_total, 2);
        $quote->total = round($request->total, 2);
        $quote->discount = round($request->discount_value, 2);
        $quote->discount_type = $request->discount_type;
        $quote->currency_id = $request->currency_id;
        $quote->status = $request->status;
        $quote->note = trim_editor($request->note);
        $quote->description = trim_editor($request->description);
        $quote->estimate_number = $request->estimate_number;
        $quote->save();

        /*
            Step1 - Delete all items which are not avaialable
            Step2 - Find old items, update it and check if images are newer or older
            Step3 - Insert new items with images
        */

        if (!empty($request->item_name) && is_array($request->item_name)) {
            // Step1 - Delete all invoice items which are not avaialable
            if (!empty($item_ids)) {
                EstimateItem::whereNotIn('id', $item_ids)->where('estimate_id', $quote->id)->delete();
            }else {
                // If `item_ids` is empty, delete all items for the quote
                EstimateItem::where('estimate_id', $quote->id)->delete();
            }

            // Step2&3 - Find old invoices items, update it and check if images are newer or older
            foreach ($items as $key => $item) {
                $invoice_item_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                try {
                    $estimateItem = EstimateItem::findOrFail($invoice_item_id);
                } catch (Exception) {
                    $estimateItem = new EstimateItem();
                }

                $estimateItem->estimate_id = $quote->id;
                $estimateItem->item_name = $item;
                $estimateItem->item_summary = $itemsSummary[$key];
                $estimateItem->type = 'item';
                $estimateItem->unit_id = (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null;
                $estimateItem->product_id = (isset($service / extra[$key]) && !is_null($service / extra[$key])) ? $service / extra[$key] : null;
                $estimateItem->hsn_sac_code = (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null;
                $estimateItem->quantity = $quantity[$key];
                $estimateItem->unit_price = round($cost_per_item[$key], 2);
                $estimateItem->amount = round($amount[$key], 2);
                $estimateItem->taxes = ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null);
                $estimateItem->field_order = $key + 1;
                $estimateItem->save();

                /* Invoice file save here */
                if ((isset($invoice_item_image[$key]) && $request->hasFile('invoice_item_image.' . $key)) || isset($invoice_item_image_url[$key])) {

                    /* Delete previous uploaded file if it not a service / extra (because service / extra images cannot be deleted) */
                    //phpcs:ignore
                    if (!isset($invoice_item_image_url[$key]) && $estimateItem && $estimateItem->estimateItemImage) {
                        Files::deleteFile($estimateItem->estimateItemImage->hashname, EstimateItemImage::FILE_PATH . '/' . $estimateItem->id . '/');
                    }

                    $filename = '';

                    if (isset($invoice_item_image[$key])) {
                        $filename = Files::uploadLocalOrS3($invoice_item_image[$key], EstimateItemImage::FILE_PATH . '/' . $estimateItem->id . '/');
                    }

                    EstimateItemImage::updateOrCreate(
                        [
                            'estimate_item_id' => $estimateItem->id,
                        ],
                        [
                            'filename' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getClientOriginalName() : null,
                            'hashname' => isset($invoice_item_image[$key]) ? $filename : null,
                            'size' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getSize() : null,
                            'external_link' => isset($invoice_item_image[$key]) ? null : (isset($invoice_item_image_url[$key]) ? $invoice_item_image_url[$key] : null),
                        ]
                    );
                }
            }
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $quote->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::redirect(route('quotes.index'), __('team chat.updateSuccess'));
    }

    public function destroy($id)
    {
        $quote = Quote::findOrFail($id);

        $this->deletePermission = user()->permission('delete_estimates');

        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $quote->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $quote->client_id == user()->id)
            || ($this->deletePermission == 'both' && ($quote->client_id == user()->id || $quote->added_by == user()->id))
        ));

        Quote::destroy($id);

        return Reply::success(__('team chat.deleteSuccess'));

    }

    public function download($id)
    {
        $this->invoiceSetting = invoice_setting();
        $this->quote = Quote::with('unit')->findOrFail($id)->withCustomFields();

        $getCustomFieldGroupsWithFields = $this->quote->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->viewPermission = user()->permission('view_estimates');

        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->quote->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->quote->client_id == user()->id)
            || ($this->viewPermission == 'both' && ($this->quote->client_id == user()->id || $this->quote->added_by == user()->id))
        ));

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->invoiceSetting = invoice_setting();
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        $this->quote = Quote::findOrFail($id)->withCustomFields();

        $getCustomFieldGroupsWithFields = $this->quote->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->discount = 0;

        if ($this->quote->discount > 0) {

            if ($this->quote->discount_type == 'percent') {
                $this->discount = (($this->quote->discount / 100) * $this->quote->sub_total);
            }
            else {
                $this->discount = $this->quote->discount;
            }
        }

        $taxList = array();

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $this->quote->id)
            ->get();
        $this->invoiceSetting = invoice_setting();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($this->quote->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->quote->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        if ($this->quote->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->quote->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = $this->quote->company;

        $this->invoiceSetting = invoice_setting();

        $pdf = app('dompdf.wrapper');

        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        // $pdf->loadView('quotes.pdf.' . $this->invoiceSetting->template, $this->data);
        $customCss = '<style>
                * { text-transform: none !important; }
            </style>';

        $pdf->loadHTML($customCss . view('quotes.pdf.' . $this->invoiceSetting->template, $this->data)->render());

        $filename = $this->quote->estimate_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function sendEstimate($id)
    {
        $quote = Quote::findOrFail($id);

        $quote->send_status = 1;

        if ($quote->status == 'draft') {
            $quote->status = 'waiting';
        }

        $quote->save();
        event(new NewEstimateEvent($quote));

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function changeStatus(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);
        $quote->status = 'canceled';
        $quote->save();

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function acceptModal(Request $request, $id)
    {
        return view('quotes.ajax.accept-quote', ['id' => $id]);
    }

    public function accept(EstimateAcceptRequest $request, $id)
    {
        DB::beginTransaction();

        $quote = Quote::with('sign')->findOrFail($id);

        /** @phpstan-ignore-next-line */
        if ($quote && $quote->sign) {
            return Reply::error(__('team chat.alreadySigned'));
        }

        $accept = new AcceptEstimate();
        $accept->full_name = $request->first_name . ' ' . $request->last_name;
        $accept->estimate_id = $quote->id;
        $accept->email = $request->email;
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('quote/accept');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/quote/accept/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'quote/accept', $quote->company_id);

        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'quote/accept/', 300);
            }
        }

        $accept->signature = $imageName;
        $accept->save();

        $quote->status = 'accepted';
        $quote->save();

        $invoiceExist = Invoice::where('estimate_id', $quote->id)->first();

        if (is_null($invoiceExist)) {

            $invoice = new Invoice();

            $invoice->client_id = $quote->client_id;
            $invoice->issue_date = now($this->company->timezone)->format('Y-m-d');
            $invoice->due_date = now($this->company->timezone)->addDays(invoice_setting()->due_after)->format('Y-m-d');
            $invoice->sub_total = round($quote->sub_total, 2);
            $invoice->discount = round($quote->discount, 2);
            $invoice->discount_type = $quote->discount_type;
            $invoice->total = round($quote->total, 2);
            $invoice->currency_id = $quote->currency_id;
            $invoice->note = trim_editor($quote->note);
            $invoice->status = 'unpaid';
            $invoice->estimate_id = $quote->id;
            $invoice->invoice_number = Invoice::lastInvoiceNumber() + 1;
            $invoice->save();

            /** @phpstan-ignore-next-line */
            foreach ($quote->items as $item) :
                if (!is_null($item)) {
                    InvoiceItems::create(
                        [
                            'invoice_id' => $invoice->id,
                            'item_name' => $item->item_name,
                            'item_summary' => $item->item_summary ?: '',
                            'type' => 'item',
                            'quantity' => $item->quantity,
                            'unit_price' => round($item->unit_price, 2),
                            'amount' => round($item->amount, 2),
                            'taxes' => $item->taxes
                        ]
                    );
                }

            endforeach;

            // Log search
            $this->logSearchEntry($invoice->id, $invoice->invoice_number, 'invoices.show', 'invoice');
        }


        DB::commit();

        return Reply::redirect(route('quotes.index'), __('team chat.estimateSigned'));
    }

    public function decline(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);
        $quote->status = 'declined';
        $quote->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function deleteEstimateItemImage(Request $request)
    {
        $item = EstimateItemImage::where('estimate_item_id', $request->invoice_item_id)->first();

        if ($item) {
            Files::deleteFile($item->hashname, 'quote-files/' . $item->id . '/');
            $item->delete();
        }

        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function getclients($id)
    {
        $client_data = Service / Extra::where('unit_id', $id)->get();
        $unitId = UnitType::where('id', $id)->first();

        return Reply::dataOnly(['status' => 'success', 'data' => $client_data, 'type' => $unitId]);
    }

    public function addItem(Request $request)
    {
        $this->items = Service / Extra::findOrFail($request->id);
        $this->invoiceSetting = invoice_setting();

        $exchangeRate = Currency::findOrFail($request->currencyId);

        if (!is_null($exchangeRate) && !is_null($exchangeRate->exchange_rate) && $exchangeRate->exchange_rate > 0) {
            if ($this->items->total_amount != '') {
                /** @phpstan-ignore-next-line */
                $this->items->price = floor($this->items->total_amount / $exchangeRate->exchange_rate);
            }
            else {

                $this->items->price = floatval($this->items->price) / floatval($exchangeRate->exchange_rate);
            }
        }
        else {
            if ($this->items->total_amount != '') {
                $this->items->price = $this->items->total_amount;
            }
        }

        $this->items->price = number_format((float)$this->items->price, 2, '.', '');
        $this->taxes = Tax::all();
        $this->units = UnitType::all();
        $view = view('invoices.ajax.add_item', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

}

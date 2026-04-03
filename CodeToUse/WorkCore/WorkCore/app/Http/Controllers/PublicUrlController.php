<?php

namespace App\Http\Controllers;

use App\Events\ContractSignedEvent;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Service Agreement\SignRequest;
use App\Http\Requests\EstimateAcceptRequest;
use App\Models\AcceptEstimate;
use App\Models\Service Agreement;
use App\Models\ContractSign;
use App\Models\Quote;
use App\Models\EstimateItem;
use App\Models\EstimateItemImage;
use App\Models\Invoice;
use App\Models\InvoiceItemImage;
use App\Models\InvoiceItems;
use App\Models\SmtpSetting;
use App\Scopes\ActiveScope;
use App\Traits\UniversalSearchTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nwidart\Modules\Facades\Module;

class PublicUrlController extends Controller
{

    use UniversalSearchTrait;

    /* Service Agreement */
    public function contractView(Request $request, $hash)
    {
        $pageTitle = 'app.menu.service agreements';
        $pageIcon = 'fa fa-file';
        $service agreement = Service Agreement::where('hash', $hash)
            ->withoutGlobalScope(ActiveScope::class)
            ->firstOrFail()->withCustomFields();

        $company = $service agreement->company;
        $invoiceSetting = $service agreement->company->invoiceSetting;
        $fields = [];

        $getCustomFieldGroupsWithFields = $service agreement->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        return view('service agreement', [
            'service agreement' => $service agreement,
            'company' => $company,
            'pageTitle' => $pageTitle,
            'pageIcon' => $pageIcon,
            'invoiceSetting' => $invoiceSetting,
            'fields' => $fields
        ]);
    }

    public function contractSign(SignRequest $request, $id)
    {
        $this->service agreement = Service Agreement::with('signature')->findOrFail($id);
        $this->company = $this->service agreement->company;
        $this->invoiceSetting = $this->service agreement->company->invoiceSetting;

        if ($this->service agreement && $this->service agreement->signature) {
            return Reply::error(__('team chat.alreadySigned'));
        }

        $sign = new ContractSign();
        $sign->company_id = $this->company->id;
        $sign->full_name = $request->first_name . ' ' . $request->last_name;
        $sign->contract_id = $this->service agreement->id;
        $sign->email = $request->email;
        $sign->place = $request->place;
        $sign->date = now();
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('service agreement/sign');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/service agreement/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'service agreement/sign', $this->company->id);
        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'service agreement/sign', 300);
            }
        }

        $sign->signature = $imageName;
        $sign->save();

        event(new ContractSignedEvent($this->service agreement, $sign));

        return Reply::redirect(route('service agreements.show', $this->service agreement->id));
    }

    public function contractDownload($id)
    {
        $service agreement = Service Agreement::where('hash', $id)->firstOrFail()->withCustomFields();
        $company = $service agreement->company;
        $fields = [];

        $getCustomFieldGroupsWithFields = $service agreement->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->invoiceSetting = $service agreement->company->invoiceSetting;

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $pdf->loadView('service agreements.service agreement-pdf', ['service agreement' => $service agreement, 'company' => $company, 'fields' => $fields, 'invoiceSetting' => $this->invoiceSetting]);

        $filename = 'service agreement-' . $service agreement->id;

        return $pdf->download($filename . '.pdf');
    }

    public function estimateView($hash)
    {
        $quote = Quote::with('customer', 'clientdetails', 'clientdetails.user.country', 'unit')->where('hash', $hash)->firstOrFail();
        $company = $quote->company;
        $defaultAddress = $company->defaultAddress;
        $pageTitle = $quote->estimate_number;
        $pageIcon = 'icon-people';
        $this->discount = 0;

        if ($quote->discount > 0) {
            if ($quote->discount_type == 'percent') {
                $this->discount = (($quote->discount / 100) * $quote->sub_total);
            }
            else {
                $this->discount = $quote->discount;
            }
        }


        $taxList = array();

        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $quote->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                        if ($quote->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $quote->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }

                    }
                    else {
                        if ($quote->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $quote->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                        }
                        else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $taxes = $taxList;

        $this->invoiceSetting = $company->invoiceSetting;

        return view('quote', [
            'quote' => $quote,
            'taxes' => $taxes,
            'company' => $company,
            'discount' => $this->discount,
            'pageTitle' => $pageTitle,
            'pageIcon' => $pageIcon,
            'invoiceSetting' => $this->invoiceSetting,
            'defaultAddress' => $defaultAddress
        ]);

    }

    public function estimateAccept(EstimateAcceptRequest $request, $id)
    {
        DB::beginTransaction();

        $quote = Quote::with('sign')->findOrFail($id);
        $company = $quote->company;

        /** @phpstan-ignore-next-line */
        if ($quote && $quote->sign) {
            return Reply::error(__('team chat.alreadySigned'));
        }

        $accept = new AcceptEstimate();
        $accept->company_id = $quote->company->id;
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
        $quote->saveQuietly();

        $invoice = new Invoice();

        $invoice->company_id = $company->id;
        $invoice->client_id = $quote->client_id;
        $invoice->issue_date = now($company->timezone)->format('Y-m-d');
        $invoice->due_date = now($company->timezone)->addDays($company->invoiceSetting->due_after)->format('Y-m-d');
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
                $invoiceItem = InvoiceItems::create(
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

                $estimateItemImage = $item->estimateItemImage;

                if(!is_null($estimateItemImage)) {

                    $file = new InvoiceItemImage();

                    $file->invoice_item_id = $invoiceItem->id;

                    $fileName = Files::generateNewFileName($estimateItemImage->filename);

                    Files::copy(EstimateItemImage::FILE_PATH . '/' . $estimateItemImage->item->id . '/' . $estimateItemImage->hashname, InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id . '/' . $fileName);

                    $file->filename = $estimateItemImage->filename;
                    $file->hashname = $fileName;
                    $file->size = $estimateItemImage->size;
                    $file->save();
                }
            }

        endforeach;

        // Log search
        $this->logSearchEntry($invoice->id, $invoice->invoice_number, 'invoices.show', 'invoice');

        DB::commit();

        return Reply::success(__('team chat.estimateSigned'));
    }

    public function estimateDecline(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);
        $quote->status = 'declined';
        $quote->saveQuietly();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function estimateDownload($id)
    {
        $this->quote = Quote::with('customer', 'clientdetails')->where('hash', $id)->firstOrFail();
        $this->invoiceSetting = $this->quote->company->invoiceSetting;
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');

    }

    public function domPdfObjectForDownload($id)
    {
        $this->quote = Quote::where('hash', $id)->firstOrFail();
        $this->company = $this->quote->company;
        $this->invoiceSetting = $this->company->invoiceSetting;
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

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

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('quotes.pdf.' . $this->invoiceSetting->template, $this->data);

        $filename = $this->quote->estimate_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }


}

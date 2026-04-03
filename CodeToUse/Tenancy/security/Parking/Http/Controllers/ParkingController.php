<?php

namespace Modules\Parking\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Helper\Reply;
use Modules\Parking\Entities\Parking;
use Modules\Parking\Entities\ParkingItems;
use Modules\Units\Entities\Unit;
use App\Http\Controllers\AccountBaseController;
use Modules\Parking\DataTables\ParkingDataTable;
use Modules\Parking\Http\Requests\ParkingRequest;

class ParkingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'parking::modules.parking';
    }

    public function index(ParkingDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_parking');
        abort_403(!in_array($viewPermission, ['all']));

        $this->parkir     = Parking::all();
        $this->totalUnits = count($this->parkir);
        return $dataTable->render('parking::parkir.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_parking');
        abort_403(!in_array($this->addPermission, ['all']));

        $this->pageTitle = __('parking::app.parkir.addParkir');
        $this->unit      = Unit::all();
        if (request()->ajax()) {
            $html = view('parking::parkir.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'parking::parkir.ajax.create';
        return view('parking::parkir.create', $this->data);
    }

    public function store(ParkingRequest $request)
    {
        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('parking.index');
        }

        $items = $request->item_name;
        if (empty($items)) {
            return Reply::error(__('messages.addItem'));
        }

        $parkir               = new Parking();
        $parkir->name         = $request->name;
        $parkir->no_hp        = $request->no_hp;
        $parkir->unit_id      = $request->unit_id;
        $parkir->status       = $request->status;
        $parkir->request      = $request->request_type;
        $parkir->company_name = $request->company_name;
        $parkir->save();

          // Log search
        $this->logSearchEntry($parkir->id, $parkir->name, 'parking.show', 'journal');
        return Reply::successWithData(__('parking::mesages.addParkir'), ['redirectUrl' => $redirectUrl]);
    }

    public function destroy($id)
    {
        $this->addPermission = user()->permission('delete_parking');
        abort_403(!in_array($this->addPermission, ['all']));

        $firstInvoice = Parking::orderBy('id', 'desc')->first();
        $invoice      = Parking::findOrFail($id);

        if ($firstInvoice->id == $id) {
            Parking::destroy($id);

            return Reply::success(__('parking::mesages.deleteParkir'));
        } else {
            return Reply::error(__('parking::mesages.failParkir'));
        }
    }

    public function download($id)
    {
        $this->parkir = Parking::with('items')->findOrFail($id)->withCustomFields();
        $this->total  = $this->parkir->items->sum('biaya');
        $pdfOption    = $this->domPdfObjectForDownload($id);
        $pdf          = $pdfOption['pdf'];
        $filename     = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf'): $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->parkir = Parking::with('items')->findOrFail($id)->withCustomFields();
        $this->total  = $this->parkir->items->sum('biaya');
        $pdf          = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('parking::parkir.pdf.invoice', $this->data);
        $filename = 'request-parkir-' . $this->parkir->name;

        return [
            'pdf'      => $pdf,
            'fileName' => $filename
        ];
    }

    public function edit($id)
    {
        $this->addPermission = user()->permission('edit_parking');
        abort_403(!in_array($this->addPermission, ['all']));

        $this->parkir    = Parking::with('items')->findOrFail($id)->withCustomFields();
        $this->pageTitle = __('parking::app.parkir.editParkir');
        $this->unit      = Unit::all();
        return view('parking::parkir.ajax.edit', $this->data);
    }

    public function update(ParkingRequest $request, $id)
    {
        $parkir               = Parking::findOrFail($id);
        $parkir->name         = $request->name;
        $parkir->no_hp        = $request->no_hp;
        $parkir->unit_id      = $request->unit_id;
        $parkir->status       = $request->status;
        $parkir->request      = $request->request_type;
        $parkir->company_name = $request->company_name;
        $parkir->save();

          // Update detail
        if (!empty(request()->item_name) && is_array(request()->item_name)) {

            $items           = request()->item_name;
            $jenis_kendaraan = request()->jenis_kendaraan;
            $no_plat_lama    = request()->no_plat_lama;
            $no_plat_baru    = request()->no_plat_baru;
            $cost_per_item   = request()->cost_per_item;
            $item_ids        = request()->item_ids;

              // Step1 - Delete all invoice items which are not avaialable
            if (!empty($item_ids)) {
                ParkingItems::whereNotIn('id', $item_ids)->where('parkir_id', $parkir->id)->delete();
            }

              // Step2&3 - Find old invoices items, update it and check if images are newer or older
            foreach ($items as $key => $item) {
                $invoice_item_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                try {
                    $invoiceItem = ParkingItems::findOrFail($invoice_item_id);
                } catch (Exception) {
                    $invoiceItem = new ParkingItems();
                }

                $invoiceItem->parkir_id       = $parkir->id;
                $invoiceItem->jenis_kendaraan = $jenis_kendaraan;
                $invoiceItem->jumlah_periode  = $item;
                $invoiceItem->no_plat_lama    = $no_plat_lama[$key];
                $invoiceItem->no_plat_baru    = $no_plat_baru[$key];
                $invoiceItem->biaya           = $cost_per_item[$key];
                $invoiceItem->saveQuietly();
            }
        }

        return Reply::redirect(route('parking.index'), __('parking::mesages.updateParkir'));
    }

    public function show($id)
    {
        $this->addPermission = user()->permission('view_parking');
        abort_403(!in_array($this->addPermission, ['all']));

        $this->parkir    = Parking::with('items')->findOrFail($id)->withCustomFields();
        $this->pageTitle = __('parking::app.parkir.showParkir');
        $this->unit      = Unit::all();
        return view('parking::parkir.show', $this->data);
    }
}

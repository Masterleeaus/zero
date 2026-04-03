<?php

namespace Modules\TrPackage\Http\Controllers;

use Exception;
use App\Helper\Files;
use Carbon\Carbon;
use App\Helper\Reply;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use Modules\Units\Entities\Unit;
use Modules\TrAccessCard\Entities\TrAccessCard;
use App\Http\Controllers\AccountBaseController;
use Modules\TrPackage\DataTables\ReceiveDataTable;
use Modules\TrPackage\Entities\Package;
use Modules\TrPackage\Entities\PackageItems;
use Modules\TrPackage\Entities\Ekspedisi;
use Modules\TrPackage\Entities\TypePackage;
use Modules\TrPackage\Http\Requests\CardRequest;

class ReceivingPackageController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'trpackage::modules.receive';
        $this->pageIcon  = 'ti-settings';
    }
      /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(ReceiveDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_package');
        abort_403(!in_array($viewPermission, ['all']));

        $this->card        = PackageItems::all();
        $this->units       = Unit::all();
        $this->ekspedisi   = Ekspedisi::all();

        return $dataTable->render('trpackage::penerimaan.index', $this->data);
    }

      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $viewPermission = user()->permission('add_package');
        abort_403(!in_array($viewPermission, ['all']));

        $this->pageTitle = __('trpackage::app.receive.addReceive');
        $this->units     = Unit::all();
        $this->ekspedisi = Ekspedisi::all();
        $this->typePackage= TypePackage::all();

        if (request()->ajax()) {
            $html = view('trpackage::penerimaan.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trpackage::penerimaan.ajax.create';
        return view('trpackage::penerimaan.create', $this->data);
    }

      /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(Request $request)
    {
        $items = $request->unit_id;
        if (empty($items)) {
            return Reply::error(__('messages.addItem'));
        }

        $data['tanggal_diterima'] = Carbon::createFromFormat($this->company->date_format, $request->input('tanggal_diterima'))->format('Y-m-d');
        $data['ekspedisi_id']     = $request->ekspedisi;
        $data['no_hp_pengirim']   = $request->no_hp_pengirim;
        $data['nama_pengirim']    = $request->nama_pengirim;
        $data['jam']              = $request->jam;
        $data['catatan_penerima'] = $request->catatan_penerima;
        $data['status_ambil']     = 'new';

        if ($request->hasFile('foto_penerima')) {
            $data['foto_penerima'] = Files::upload($request->foto_penerima, 'barang', 300);
        }
        $barang      = Package::create($data);

        $redirectUrl = route('receive.index');
        return Reply::successWithData(__('trpackage::messages.addReceive'), ['redirectUrl' => $redirectUrl]);
    }

      /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_package');
        abort_403(!in_array($viewPermission, ['all']));

        $this->pageTitle = __('trpackage::app.receive.showReceive');

        $this->card = Package::with('unit','ekspedisi','type')->findOrFail($id);
        $this->url  = asset_url('barang/' . $this->card->foto_penerima);

        if (request()->ajax()) {
            $html = view('trpackage::penerimaan.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trpackage::penerimaan.ajax.show';
        return view('trpackage::penerimaan.create', $this->data);
    }

      /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewPermission = user()->permission('edit_package');
        abort_403(!in_array($viewPermission, ['all']));

        $this->pageTitle = __('trpackage::app.receive.editReceive');
        $this->card      = Package::with('items')->findOrFail($id);
        $this->units     = Unit::all();
        $this->ekspedisi = Ekspedisi::all();
        $this->typePackage= TypePackage::all();
        $this->url       = asset_url('paket/' . $this->card->foto_penerima);

        if (request()->ajax()) {
            $html = view('trpackage::penerimaan.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trpackage::penerimaan.ajax.edit';
        return view('trpackage::penerimaan.create', $this->data);
    }

      /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $editUnit = user()->permission('edit_package');
        abort_403($editUnit != 'all');

        $barang                   = Package::findOrFail($id);
        $data['tanggal_diterima'] = Carbon::createFromFormat($this->company->date_format, $request->input('tanggal_diterima'))->format('Y-m-d');
        $data['ekspedisi_id']     = $request->ekspedisi;
        $data['no_hp_pengirim']   = $request->no_hp_pengirim;
        $data['nama_pengirim']    = $request->nama_pengirim;
        $data['jam']              = $request->jam;
        $data['catatan_penerima'] = $request->catatan_penerima;
        $data['status_ambil']     = $request->status_ambil;
        if ($request->image_delete == 'yes') {
            Files::deleteFile($barang->foto_penerima, 'barang');
            $data['foto_penerima'] = null;
        }

        if ($request->hasFile('foto_penerima')) {
            Files::deleteFile($barang->foto_penerima, 'barang');
            $data['foto_penerima'] = Files::upload($request->foto_penerima, 'barang', 300);
        }
        $barang->update($data);

        // Update detail
        if (!empty(request()->unit_id) && is_array(request()->unit_id)) {

            $nama_penerima    = request()->nama_penerima;
            $unit_id          = request()->unit_id;
            $jenis_barang     = request()->jenis_barang;
            $item_ids         = request()->item_ids;

            // Step1 - Delete all invoice nama_penerima which are not avaialable
            if (!empty($item_ids)) {
                PackageItems::whereNotIn('id', $item_ids)->where('Package_id', $barang->id)->delete();
            }

            // Step2&3 - Find old invoices items, update it and check if images are newer or older
            foreach ($nama_penerima as $key => $item) {
                $invoice_item_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                try {
                    $invoiceItem = PackageItems::findOrFail($invoice_item_id);
                } catch (Exception) {
                    $invoiceItem = new PackageItems();
                }

                $invoiceItem->company_id    = company()->id;
                $invoiceItem->Package_id     = $barang->id;
                $invoiceItem->nama_penerima = $nama_penerima[$key];
                $invoiceItem->type_id       = $jenis_barang[$key];
                $invoiceItem->unit_id       = $unit_id[$key];
                $invoiceItem->saveQuietly();
            }
        }
        $redirectUrl = route('receive.index');
        return Reply::successWithData(__('trpackage::messages.updateReceive'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);
                return Reply::success(__('trpackage::messages.deleteReceive'));
            default:
                return Reply::error(__('trpackage::messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        Package::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

      /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $viewPermission = user()->permission('delete_package');
        abort_403(!in_array($viewPermission, ['all']));
        Package::destroy($id);

        $redirectUrl = route('receive.index');
        return Reply::successWithData(__('trpackage::messages.deleteReceive'), ['redirectUrl' => $redirectUrl]);
    }

    public function download($id)
    {
        $this->card        = Package::with('unit')->findOrFail($id);
        $this->unit_detail = Unit::with('floor', 'tower')->findOrFail($this->card->unit_id);
        $pdfOption         = $this->domPdfObjectForDownload($id);
        $pdf               = $pdfOption['pdf'];
        $filename          = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf'): $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->card        = Package::with('unit')->findOrFail($id);
        $this->unit_detail = Unit::findOrFail($this->card->unit_id);
        $pdf               = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('trpackage::penerimaan.pdf.invoice', $this->data);
        $filename = 'card-access-' . $this->card->name;

        return [
            'pdf'      => $pdf,
            'fileName' => $filename
        ];
    }
}

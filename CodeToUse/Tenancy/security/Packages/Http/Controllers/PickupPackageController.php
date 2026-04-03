<?php

namespace Modules\TrPackage\Http\Controllers;

use App\Helper\Files;
use Carbon\Carbon;
use App\Helper\Reply;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use Modules\Units\Entities\Unit;
use Modules\TrPackage\Entities\Package;
use App\Http\Controllers\AccountBaseController;
use Modules\TrPackage\DataTables\PickupDataTable;
use Modules\TrPackage\Entities\PackageItems;
use Modules\TrPackage\Entities\Ekspedisi;
use Modules\TrPackage\Entities\TypePackage;

class PickupPackageController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'trpackage::modules.pickup';
        $this->pageIcon  = 'ti-settings';
    }
      /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(PickupDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_package');
        abort_403(!in_array($viewPermission, ['all']));

        $this->units = Unit::all();

        return $dataTable->render('trpackage::pengambilan.index', $this->data);
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

        $this->pageTitle = __('trpackage::app.pickup.showPickup');

        $this->card      = PackageItems::with('unit','ekspedisi','type','paket')->findOrFail($id);
        $this->url       = asset_url('paket/' . $this->card->paket->foto_penerima);
        $this->url_ambil = asset_url('paket/' . $this->card->foto_pengambil);

        if (request()->ajax()) {
            $html = view('trpackage::pengambilan.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trpackage::pengambilan.ajax.show';
        return view('trpackage::pengambilan.create', $this->data);
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

        $this->pageTitle = __('trpackage::app.pickup.editPickup');
        $this->card      = PackageItems::with('paket')->findOrFail($id);
        $this->units     = Unit::all();
        $this->ekspedisi = Ekspedisi::all();
        $this->typePackage= TypePackage::all();
        $this->url       = asset_url('paket/' . $this->card->paket->foto_penerima);
        $this->url_ambil = asset_url('paket/' . $this->card->foto_pengambil);

        if (request()->ajax()) {
            $html = view('trpackage::pengambilan.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trpackage::pengambilan.ajax.edit';
        return view('trpackage::pengambilan.create', $this->data);
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

        $paket_item                    = PackageItems::findOrFail($id);
        $data['lantai']            = $request->lantai;
        $data['status_ambil']      = $request->status_ambil;
        $data['nama_pengambil']    = $request->nama_pengambil;
        $data['no_hp_pengambil']   = $request->no_hp_pengambil;
        $data['id_card_pengambil'] = $request->id_card_pengambil;
        $data['tanggal_pengambil'] = Carbon::createFromFormat($this->company->date_format, $request->input('tanggal_pengambil'))->format('Y-m-d');
        $data['jam_ambil']         = $request->jam_ambil;
        $data['catatan_pengambil'] = $request->catatan_pengambil;
        if ($request->image_delete == 'yes') {
            Files::deleteFile($paket_item->foto_pengambil, 'paket');
            $data['foto_pengambil'] = null;
        }

        if ($request->hasFile('foto_pengambil')) {

            Files::deleteFile($paket_item->foto_pengambil, 'paket');
            $data['foto_pengambil'] = Files::upload($request->foto_pengambil, 'paket', 300);
        }

        $paket_item->update($data);
        $redirectUrl = route('pickup.index');
        return Reply::successWithData(__('trpackage::messages.updatePickup'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);
                return Reply::success(__('trpackage::messages.deletePickup'));
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

        $redirectUrl = route('accountings.index');
        return Reply::successWithData(__('trpackage::messages.deletePickup'), ['redirectUrl' => $redirectUrl]);
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

        $pdf->loadView('trpackage::pengambilan.pdf.invoice', $this->data);
        $filename = 'pickup-Package-' . $this->card->name;

        return [
            'pdf'      => $pdf,
            'fileName' => $filename
        ];
    }
}

<?php

namespace Modules\TrInOutPermit\Http\Controllers;

use Carbon\Carbon;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use Modules\Units\Entities\Unit;
use Illuminate\Support\Facades\DB;
use Modules\TrInOutPermit\Entities\TrInOutPermit;
use App\Http\Controllers\AccountBaseController;
use Modules\TrInOutPermit\DataTables\TrInOutPermitDataTable;
use Modules\TrInOutPermit\Http\Requests\StoreTrInOutPermit;

class TrInOutPermitPermissionController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'trinoutpermit::app.menu.trinoutpermit';
        $this->pageIcon  = 'ti-settings';
    }
      /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(TrInOutPermitDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_trinoutpermit');
        abort_403(!in_array($viewPermission, ['all','owned']));

        $this->tenan = TrInOutPermit::all();
        $this->units = Unit::all();

        return $dataTable->render('trinoutpermit::permission.index', $this->data);
    }

      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $viewPermission = user()->permission('add_trinoutpermit');
        abort_403(!in_array($viewPermission, ['all','owned']));

        $this->pageTitle = __('trinoutpermit::app.trinoutpermit.addTrInOutPermit');
        $this->units     = Unit::all();
        $this->notes     = DB::table('notes')
            ->where('table_name', 'tr_in_out_permit')
            ->get();

        if (request()->ajax()) {
            $html = view('trinoutpermit::permission.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trinoutpermit::permission.ajax.create';

        return view('trinoutpermit::permission.create', $this->data);
    }

      /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreTrInOutPermit $request)
    {
          // create new user
        $d                  = new TrInOutPermit();
        $d->pembawa_brg     = $request->input('pembawa_brg');
        $d->date            = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
        $d->jam             = Carbon::createFromFormat('h:i a', $request->jam)->format('h:i a');
        $d->pj              = $request->input('pj');
        $d->name            = $request->input('name');
        $d->unit_id         = $request->input('unit_id');
        $d->keterangan      = $request->input('keterangan');
        $d->jenis_barang    = $request->input('jenis_barang');
        $d->no_hp           = $request->input('no_hp');
        $d->identity        = $request->input('identity');
        $d->identity_number = $request->input('identity_number');
        $d->created_by      = user()->id;
        $d->save();

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
                $redirectUrl = route('trinoutpermit.index');
        }

        return Reply::successWithData(__('trinoutpermit::messages.addTrInOutPermit'), ['redirectUrl' => $redirectUrl]);
    }

      /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_trinoutpermit');
        abort_403(!in_array($viewPermission, ['all','owned']));

        $this->pageTitle = __('trinoutpermit::app.trinoutpermit.showTrInOutPermit');
        $this->tenancy   = TrInOutPermit::with('unit', 'approved')->findOrFail($id);
        $this->url       = asset_url('validasi-tenancy/' . $this->tenancy->validate_img);
        $this->notes     = DB::table('notes')
            ->where('table_name', 'tr_in_out_permit')
            ->get();

        if (request()->ajax()) {
            $html = view('trinoutpermit::permission.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trinoutpermit::permission.ajax.show';
        return view('trinoutpermit::permission.create', $this->data);
    }

      /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewPermission = user()->permission('edit_trinoutpermit');
        abort_403(!in_array($viewPermission, ['all','owned']));

        $this->pageTitle = __('trinoutpermit::app.trinoutpermit.editTrInOutPermit');
        $this->tenancy   = TrInOutPermit::findOrFail($id);
        $this->units     = Unit::all();


        if (request()->ajax()) {
            $html = view('trinoutpermit::permission.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trinoutpermit::permission.ajax.edit';
        return view('trinoutpermit::permission.create', $this->data);
    }

      /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(StoreTrInOutPermit $request, $id)
    {
        $viewPermission = user()->permission('edit_trinoutpermit');
        abort_403(!in_array($viewPermission, ['all','owned']));

        $d               = TrInOutPermit::find($id);
        $d->pembawa_brg  = $request->input('pembawa_brg');
        $d->date            = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
        $d->jam             = Carbon::createFromFormat('h:i a', $request->jam)->format('h:i a');
        $d->pj           = $request->input('pj');
        $d->name         = $request->input('name');
        $d->jam         = $request->input('jam');
        $d->unit_id      = $request->input('unit_id');
        $d->keterangan   = $request->input('keterangan');
        $d->jenis_barang = $request->input('jenis_barang');
        $d->no_hp        = $request->input('no_hp');
        $d->save();

        $redirectUrl = route('trinoutpermit.index');
        return Reply::successWithData(__('trinoutpermit::messages.updateTrInOutPermit'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);
                return Reply::success(__('trinoutpermit::messages.deleteTrInOutPermit'));
            default:
                return Reply::error(__('trinoutpermit::messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        TrInOutPermit::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

      /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $viewPermission = user()->permission('delete_trinoutpermit');
        abort_403(!in_array($viewPermission, ['all','owned']));

        TrInOutPermit::destroy($id);

        $redirectUrl = route('trinoutpermit.index');
        return Reply::successWithData(__('trinoutpermit::messages.deleteTrInOutPermit'), ['redirectUrl' => $redirectUrl]);
    }

    public function download($id)
    {
        $this->tenancy     = TrInOutPermit::with('unit')->findOrFail($id);
        $this->unit_detail = Unit::with('floor', 'tower')->findOrFail($this->tenancy->unit_id);
        $pdfOption         = $this->domPdfObjectForDownload($id);
        $pdf               = $pdfOption['pdf'];
        $filename          = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf'): $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->tenancy     = TrInOutPermit::with('unit')->findOrFail($id);
        $this->unit_detail = Unit::findOrFail($this->tenancy->unit_id);
        $pdf               = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('trinoutpermit::permission.pdf.invoice', $this->data);
        $filename = 'permission-' . $this->tenancy->name;

        return [
            'pdf'      => $pdf,
            'fileName' => $filename
        ];
    }

    public function approved($id)
    {
        $mr              = TrInOutPermit::findOrFail($id);
        $mr->approved_by = user()->id;
        $mr->approved_at = Carbon::now();
        $mr->status_approve = True;
        $mr->save();

        $redirectUrl = route('trinoutpermit.index');
        return redirect($redirectUrl)->with('success', __('trinoutpermit::messages.updateTrInOutPermit'));
          // return Reply::successWithData(__('trinoutpermit::messages.updateTrInOutPermit'), ['redirectUrl' => $redirectUrl]);
    }

    public function approved_bm($id)
    {
        $mr      = TrInOutPermit::findOrFail($id);
        $mr->approved_bm = user()->id;
        $mr->approved_bm_at = Carbon::now();
        $mr->status_approve_bm = True;
        $mr->save();

        $redirectUrl = route('trinoutpermit.index');
        return redirect($redirectUrl)->with('success', __('trinoutpermit::messages.updateTrInOutPermit'));
          // return Reply::successWithData(__('trinoutpermit::messages.updateTrInOutPermit'), ['redirectUrl' => $redirectUrl]);
    }

    public function validateData($id)
    {
        $editUnit = user()->permission('edit_trinoutpermit');
        abort_403($editUnit != 'all');

        $this->pageTitle = __('trinoutpermit::app.trinoutpermit.validateTrInOutPermit');
        $this->tenancy   = TrInOutPermit::findOrFail($id);
        $this->units     = Unit::all();


        if (request()->ajax()) {
            $html = view('trinoutpermit::permission.ajax.validate', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'trinoutpermit::permission.ajax.validate';
        return view('trinoutpermit::permission.create', $this->data);
    }

    public function processValidatedData(Request $request, $id)
    {
        $editUnit = user()->permission('edit_trinoutpermit');
        abort_403($editUnit != 'all');

        $tenancy                  = TrInOutPermit::findOrFail($id);
        $data['validated_by']     = user()->id;
        $data['validated_remark'] = $request->remark;
        $data['validated_at']     = Carbon::now();

        if ($request->image_delete == 'yes') {
            Files::deleteFile($tenancy->image, 'validasi-tenancy');
            $data['validate_img'] = null;
        }

        if ($request->hasFile('image')) {
            Files::deleteFile($tenancy->image, 'validasi-tenancy');
            $data['validate_img'] = Files::upload($request->image, 'validasi-tenancy', 300);
        }

        $tenancy->update($data);

        $redirectUrl = route('trinoutpermit.index');
        return Reply::successWithData(__('trinoutpermit::messages.updateTrInOutPermit'), ['redirectUrl' => $redirectUrl]);
    }
}

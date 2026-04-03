<?php

namespace Modules\Security\Http\Controllers;

use Carbon\Carbon;
use App\Helper\Files;
use App\Helper\Reply;
use Modules\Units\Entities\Unit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AccountBaseController;
use Modules\TrInOutPermit\Entities\TrInOutPermit;
use Modules\Security\DataTables\SecurityDataTable;
use Modules\Security\Http\Requests\GoodsInOutValidation;

class SecurityController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'security::modules.securityTF';
        $this->pageIcon  = 'ti-settings';
    }

    public function index(SecurityDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_security');
        abort_403(!in_array($viewPermission, ['all']));

        $this->security = TrInOutPermit::all();
        $this->units = Unit::all();

        return $dataTable->render('security::transfer.index', $this->data);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_security');
        abort_403(!in_array($viewPermission, ['all']));

        $this->pageTitle = __('security::app.menu.goodsInOutValidation');
        $this->security   = TrInOutPermit::with('unit', 'approved')->findOrFail($id);
        $this->url       = asset_url('validasi-security/' . $this->security->validate_img);
        $this->notes     = DB::table('notes')
            ->where('table_name', 'security_izin_brg')
            ->get();

        if (request()->ajax()) {
            $html = view('security::transfer.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'security::transfer.ajax.show';
        return view('security::transfer.create', $this->data);
    }

    public function download($id)
    {
        $this->security     = TrInOutPermit::with('unit')->findOrFail($id);
        $this->unit_detail = Unit::with('floor', 'tower')->findOrFail($this->security->unit_id);
        $pdfOption         = $this->domPdfObjectForDownload($id);
        $pdf               = $pdfOption['pdf'];
        $filename          = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf'): $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->security     = TrInOutPermit::with('unit')->findOrFail($id);
        $this->unit_detail = Unit::findOrFail($this->security->unit_id);
        $pdf               = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('security::transfer.pdf.invoice', $this->data);
        $filename = 'permission-' . $this->security->name;

        return [
            'pdf'      => $pdf,
            'fileName' => $filename
        ];
    }

    public function validateData($id)
    {
        $editUnit = user()->permission('edit_security');
        abort_403($editUnit != 'all');
        
        $this->pageTitle = __('security::app.menu.goodsInOutValidation');
        $this->security   = TrInOutPermit::findOrFail($id);
        $this->units     = Unit::all();


        if (request()->ajax()) {
            $html = view('security::transfer.ajax.validate', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'security::transfer.ajax.validate';
        return view('security::transfer.create', $this->data);
    }

    public function processValidatedData(GoodsInOutValidation $request, $id)
    {
        $editUnit = user()->permission('edit_security');
        abort_403($editUnit != 'all');

        $security                  = TrInOutPermit::findOrFail($id);
        // $data = $request->all();
        $data['validated_by']     = user()->id;
        $data['validate_remark'] = $request->remark;
        $data['validated_at']     = Carbon::now();
        $data['status_validated']     = True;

        if ($request->image_delete == 'yes') {
            Files::deleteFile($security->image, 'validasi-security');
            $data['validate_img'] = null;
        }

        if ($request->hasFile('image')) {
            Files::deleteFile($security->image, 'validasi-security');
            $data['validate_img'] = Files::upload($request->image, 'validasi-security', 300);
        }

        $security->update($data);

        $redirectUrl = route('security-transfer.index');
        return Reply::successWithData(__('security::messages.validateSuccess'), ['redirectUrl' => $redirectUrl]);
    }
}

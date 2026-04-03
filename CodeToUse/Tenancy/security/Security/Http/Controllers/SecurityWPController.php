<?php
namespace Modules\Security\Http\Controllers;

use Carbon\Carbon;
use App\Helper\Files;
use App\Helper\Reply;
use Illuminate\Http\Request;
use Modules\Units\Entities\Unit;
use Illuminate\Support\Facades\DB;
use Modules\TrWorkPermits\Entities\WorkPermits;
use App\Http\Controllers\AccountBaseController;
use Modules\Security\DataTables\SecurityWPDataTable;

class SecurityWPController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'security::modules.securityWP';
        $this->pageIcon  = 'ti-settings';
    }

    public function index(SecurityWPDataTable $WPataTable)
    {
        $viewPermission = user()->permission('view_security');
        abort_403(!in_array($viewPermission, ['all']));

        $this->security = WorkPermits::all();
        return $WPataTable->render('security::work-permit.index', $this->data);
    }

    public function show($id)
    {
        $editUnit = user()->permission('view_security');
        abort_403($editUnit != 'all');

        $this->pageTitle = __('security::app.workpermits.show');
        $this->wp        = WorkPermits::with('unit', 'approved', 'validated')->findOrFail($id);
        $this->url       = asset_url('validasi-security/' . $this->wp->validated_img);
        $this->notes     = DB::table('notes')
            ->where('table_name', 'workpermits')
            ->get();

        if (request()->ajax()) {
            $html = view('security::work-permit.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'security::work-permit.ajax.show';
        return view('security::work-permit.create', $this->data);
    }

    public function download($id)
    {
        $this->security     = WorkPermits::with('unit')->findOrFail($id);
        $this->unit_detail = Unit::with('floor', 'tower')->findOrFail($this->security->unit_id);
        $pdfOption         = $this->domPdfObjectForDownload($id);
        $pdf               = $pdfOption['pdf'];
        $filename          = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf'): $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->security     = WorkPermits::with('unit')->findOrFail($id);
        $this->unit_detail = Unit::findOrFail($this->security->unit_id);
        $pdf               = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('security::work-permit.pdf.invoice', $this->data);
        $filename = 'permission-' . $this->security->name;

        return [
            'pdf'      => $pdf,
            'fileName' => $filename
        ];
    }

    public function validateData($id)
    {
        $this->pageTitle = __('security::app.wp.validate');
        $this->wp        = WorkPermits::with('unit')->findOrFail($id);
        $this->units     = Unit::all();

        if (request()->ajax()) {
            $html = view('security::work-permit.ajax.validate', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'security::work-permit.ajax.validate';
        return view('security::work-permit.create', $this->data);
    }

    public function processValidatedData(Request $request, $id)
    {
        $editUnit = user()->permission('edit_security');
        abort_403($editUnit != 'all');

        $wp                     = WorkPermits::findOrFail($id);
        $data['validated_by']     = user()->id;
        $data['validated_remark'] = $request->remark;
        $data['validated_at']     = Carbon::now();
        $data['status_validated']     = True;

        if ($request->image_delete == 'yes') {
            Files::deleteFile($wp->image, 'validasi-security');
            $data['validated_img'] = null;
        }

        if ($request->hasFile('image')) {
            Files::deleteFile($wp->image, 'validasi-security');
            $data['validated_img'] = Files::upload($request->image, 'validasi-security', 300);
        }

        $wp->update($data);

        $redirectUrl = route('security-workpermit.index');
        return Reply::successWithData(__('security::messages.validateSuccess'), ['redirectUrl' => $redirectUrl]);
    }
}

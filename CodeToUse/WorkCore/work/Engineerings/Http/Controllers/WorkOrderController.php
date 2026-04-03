<?php

namespace Modules\Engineerings\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Modules\Units\Entities\Unit;
use Modules\Complaint\Entities\Complaint;
use Modules\Engineerings\Entities\WorkOrder;
use Modules\Engineerings\Entities\WorkRequest;
use App\Http\Controllers\AccountBaseController;
use Modules\Assets\Entities\Assets;
use Modules\Engineerings\Entities\WorkOrderFile;
use Modules\Engineerings\DataTables\WorkOrderDataTable;
use Modules\Engineerings\Http\Requests\WorkOrderRequest;
use Modules\Houses\Entities\Area;

class WorkOrderController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'engineerings::modules.wo';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('engineerings', $this->user->modules));
            return $next($request);
        });
    }

    public function index(WorkOrderDataTable $dataTable)
    {
        $this->permissions = user()->permission('view_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->wo         = WorkOrder::all();
        $this->totalUnits = count($this->wo);
        return $dataTable->render('engineerings::workorder.index', $this->data);
    }

    public function create()
    {
        $this->permissions = user()->permission('add_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.wo.addWO');
        $this->number    = WorkOrder::lastInvoiceNumber() + 1;
        $this->zero      = '';
        if (strlen($this->number) < 4) {
            for ($i = 0; $i < 4 - strlen($this->number); $i++) {
                $this->zero = '0' . $this->zero;
            }
        }
        $this->nomor   = 'WO-' . Carbon::now()->format('ym') . '-' . $this->zero . $this->number;
        $this->ticket  = Complaint::all();
        $this->invoice = Invoice::all();
        $this->wr      = WorkRequest::all();
        $this->unit    = Unit::all();
        $this->areas   = Area::all();

        if (request()->ajax()) {
            $html = view('engineerings::workorder.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'engineerings::workorder.ajax.create';
        return view('engineerings::workorder.create', $this->data);
    }

    public function store(WorkOrderRequest $request)
    {
        $wo                   = new WorkOrder();
        $wo->workrequest_id   = $request->workrequest_id;
        $wo->complaint_id     = $request->complaint_id;
        $wo->invoice_id       = $request->invoice_id;
        $wo->nomor_wo         = $request->nomor_wo;
        $wo->category         = $request->category;
        $wo->priority         = $request->priority;
        $wo->status           = $request->status;
        $wo->work_description = $request->work_description;
        $wo->schedule_start   = $request->schedule_start;
        $wo->schedule_finish  = $request->schedule_finish;
        $wo->estimate_hours   = $request->estimate_hours;
        $wo->estimate_minutes = $request->estimate_minutes;
        $wo->actual_start     = $request->actual_start;
        $wo->actual_finish    = $request->actual_finish;
        $wo->actual_hours     = $request->actual_hours;
        $wo->actual_minutes   = $request->actual_minutes;
        $wo->completion_notes = $request->completion_notes;
        $wo->problem          = $request->problem;
        $wo->unit_id          = $request->unit_id;
        $wo->assets_id        = $request->assets_id;
        $wo->created_by       = user()->id;
        $wo->save();

        $redirectUrl = route('work.index');
        return Reply::successWithData(__('engineerings::messages.addWO'), ['workorderID' => $wo->id]);
    }

    public function destroy($id)
    {
        $this->permissions = user()->permission('delete_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $project = WorkOrder::findOrFail($id);
        Files::deleteDirectory(WorkOrderFile::FILE_PATH . '/' . $id);
        $project->forceDelete();
        return Reply::success(__('engineerings::messages.deleteWO'));
    }

    public function download($id)
    {
        $this->wo  = WorkOrder::with('wr', 'ticket', 'invoice', 'files', 'unit', 'assets.type', 'house.area')->findOrFail($id);
        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf       = $pdfOption['pdf'];
        $filename  = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf'): $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->wo = WorkOrder::with('wr', 'ticket', 'invoice', 'files', 'unit', 'assets.type', 'house.area')->findOrFail($id);
        $pdf      = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('engineerings::workorder.pdf.invoice', $this->data);
        $filename = 'work-request-' . $this->wo->nomor_wo;

        return [
            'pdf'      => $pdf,
            'fileName' => $filename,
        ];
    }

    public function edit($id)
    {
        $this->permissions = user()->permission('edit_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.wo.editWO');
        $this->wo        = WorkOrder::with('wr', 'ticket', 'invoice', 'files', 'house')->findOrFail($id);
        $this->ticket    = Complaint::all();
        $this->invoice   = Invoice::all();
        $this->wr        = WorkRequest::all();
        $this->unit      = Unit::all();
        $this->areas     = Area::all();

        return view('engineerings::workorder.ajax.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $wo                   = WorkOrder::findOrFail($id);
        $wo->workrequest_id   = $request->workrequest_id;
        $wo->complaint_id     = $request->complaint_id;
        $wo->invoice_id       = $request->invoice_id;
        $wo->nomor_wo         = $request->nomor_wo;
        $wo->category         = $request->category;
        $wo->priority         = $request->priority;
        $wo->status           = $request->status;
        $wo->work_description = $request->work_description;
        $wo->schedule_start   = $request->schedule_start;
        $wo->schedule_finish  = $request->schedule_finish;
        $wo->estimate_hours   = $request->estimate_hours;
        $wo->estimate_minutes = $request->estimate_minutes;
        $wo->actual_start     = $request->actual_start;
        $wo->actual_finish    = $request->actual_finish;
        $wo->actual_hours     = $request->actual_hours;
        $wo->actual_minutes   = $request->actual_minutes;
        $wo->completion_notes = $request->completion_notes;
        $wo->problem          = $request->problem;
        $wo->unit_id          = $request->unit_id;
        $wo->assets_id        = $request->assets_id;
        $wo->created_by       = user()->id;
        $wo->save();

        return Reply::successWithData(__('engineerings::messages.updateWO'), ['redirectUrl' => route('work.show', [$wo->id])]);
    }

    public function show($id)
    {
        $this->permissions = user()->permission('view_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.wo.showWO');
        $this->wo        = WorkOrder::with('wr', 'ticket', 'invoice', 'files', 'unit', 'assets.type', 'house.area')->findOrFail($id);
        return view('engineerings::workorder.show', $this->data);
    }

    public function getAssets($id)
    {
        $assets = ($id == 'null') ? Assets::with('type', 'unit')->get() : Assets::with('type', 'unit')->where('unit_id', $id)->get();
        return Reply::dataOnly(['status' => 'success', 'data' => $assets]);
    }
}

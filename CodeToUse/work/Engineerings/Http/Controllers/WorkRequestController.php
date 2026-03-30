<?php

namespace Modules\Engineerings\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Tax;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use Illuminate\Http\Request;
use Modules\Items\Entities\Item;
use Modules\Units\Entities\Unit;
use Modules\Complaint\Entities\Complaint;
use Modules\Engineerings\Entities\WorkItems;
use Modules\Engineerings\Entities\WorkOrder;
use Modules\Engineerings\Entities\WorkRequest;
use App\Http\Controllers\AccountBaseController;
use Modules\Engineerings\Http\Requests\WrRequest;
use Modules\Engineerings\DataTables\WorkRequestDataTable;
use Modules\Engineerings\Entities\Services;
use Modules\Engineerings\Entities\WorkServices;
use Modules\Houses\Entities\Area;
use Modules\Houses\Entities\House;

class WorkRequestController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'engineerings::modules.wr';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('engineerings', $this->user->modules));
            return $next($request);
        });
    }

    public function index(WorkRequestDataTable $dataTable)
    {
        $this->permissions = user()->permission('view_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->acc_coa    = WorkRequest::all();
        $this->type       = Item::all();
        $this->totalUnits = count($this->acc_coa);
        return $dataTable->render('engineerings::workrequest.index', $this->data);
    }

    public function create()
    {
        $this->permissions = user()->permission('add_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.wr.addWR');
        $this->number    = WorkRequest::lastInvoiceNumber() + 1;
        $this->zero      = '';
        if (strlen($this->number) < 4) {
            for ($i = 0; $i < 4 - strlen($this->number); $i++) {
                $this->zero = '0' . $this->zero;
            }
        }
        $this->nomor     = 'WR-' . Carbon::now()->format('ym') . '-' . $this->zero . $this->number;
        $this->ticket    = Complaint::all();
        $this->employees = User::allEmployees(null, true);
        $this->items_arr = Item::all();
        $this->services  = Services::all();
        $this->taxes     = Tax::all();
        $this->unit      = Unit::all();
        $this->areas     = Area::all();

        if (request()->ajax()) {
            $html = view('engineerings::workrequest.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'engineerings::workrequest.ajax.create';
        return view('engineerings::workrequest.create', $this->data);
    }

    public function store(WrRequest $request)
    {
        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('engineerings.index');
        }
        if (empty($request->items_qty)) {
            return Reply::error(__('messages.addItem'));
        }
        if (empty($request->services_qty)) {
            return Reply::error(__('messages.addItem'));
        }
        if (empty($request->type)) {
            return Reply::error(__('messages.addItem'));
        }

        if ($request->type == 'save') {
            $data['complaint_id']     = $request->complaint_id;
            $data['wr_no']            = $request->wr_no;
            $data['check_time']       = $request->check_time;
            $data['remark']           = $request->remark;
            $data['problem']          = $request->problem;
            $data['house_id']         = $request->house_id;
            $data['assign_to']        = $request->user_id;
            $data['charge_by_tenant'] = $request->charge_by_tenant === 'yes' ? 1 : ($request->charge_by_tenant === 'no' ? 0 : null);
            $data['created_by']       = user()->id;

            if ($request->hasFile('image')) {
                $data['image'] = Files::upload($request->image, 'work_requests', 300);
            }
            $wr = WorkRequest::create($data);
            return Reply::successWithData(__('engineerings::messages.addWR'), ['redirectUrl' => route('engineerings.index')]);
        } else {
            $data['complaint_id']     = $request->complaint_id;
            $data['wr_no']            = $request->wr_no;
            $data['check_time']       = $request->check_time;
            $data['remark']           = $request->remark;
            $data['problem']          = $request->problem;
            $data['house_id']         = $request->house_id;
            $data['assign_to']        = $request->user_id;
            $data['status_wo']        = 1;
            $data['charge_by_tenant'] = $request->charge_by_tenant === 'yes' ? 1 : ($request->charge_by_tenant === 'no' ? 0 : null);
            $data['created_by']       = user()->id;

            if ($request->hasFile('image')) {
                $data['image'] = Files::upload($request->image, 'work_requests', 300);
            }
            $wr = WorkRequest::create($data);

            $this->number = WorkOrder::lastInvoiceNumber() + 1;
            $this->zero   = '';
            if (strlen($this->number) < 4) {
                for ($i = 0; $i < 4 - strlen($this->number); $i++) {
                    $this->zero = '0' . $this->zero;
                }
            }
            $this->nomor        = 'WO-' . Carbon::now()->format('ym') . '-' . $this->zero . $this->number;
            $wo                 = new WorkOrder();
            $wo->workrequest_id = $wr->id;
            $wo->complaint_id   = $request->complaint_id;
            $wo->problem        = $request->problem;
            $wo->house_id       = $request->house_id;
            $wo->nomor_wo       = $this->nomor;
            $wo->created_by     = user()->id;
            $wo->save();
            return Reply::successWithData(__('engineerings::messages.addWR'), ['redirectUrl' => route('work.show', [$wo->id])]);
        }
    }

    public function destroy($id)
    {
        $this->permissions = user()->permission('delete_eng');
        abort_403(!in_array($this->permissions, ['all']));

        WorkRequest::destroy($id);
        return Reply::success(__('engineerings::messages.deleteWR'));
    }

    public function download($id)
    {
        $this->wr  = WorkRequest::with('ticket', 'items.item', 'services.service', 'user', 'house.area')->findOrFail($id);
        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf       = $pdfOption['pdf'];
        $filename  = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf'): $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->wr = WorkRequest::with('ticket', 'items.item', 'services.service', 'user', 'house.area')->findOrFail($id);
        $pdf      = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('engineerings::workrequest.pdf.invoice', $this->data);
        $filename = 'work-request-' . $this->wr->wr_no;

        return [
            'pdf'      => $pdf,
            'fileName' => $filename,
        ];
    }

    public function edit($id)
    {
        $this->permissions = user()->permission('edit_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->wr        = WorkRequest::with('items', 'services','house.area')->findOrFail($id);
        $this->pageTitle = __('engineerings::app.wr.editWR');
        $this->ticket    = Complaint::all();
        $this->employees = User::allEmployees(null, true);
        $this->items_arr = Item::all();
        $this->taxes     = Tax::all();
        $this->unit      = Unit::all();
        $this->areas     = Area::all();
        $this->houses    = House::all();
        $this->services  = Services::all();
        $this->url       = asset_url('work_requests/' . $this->wr->image);

        return view('engineerings::workrequest.ajax.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $wr                       = WorkRequest::findOrFail($id);
        $data['complaint_id']     = $request->complaint_id;
        $data['wr_no']            = $request->wr_no;
        $data['check_time']       = $request->check_time;
        $data['remark']           = $request->remark;
        $data['problem']          = $request->problem;
        $data['house_id']         = $request->house_id;
        $data['assign_to']        = $request->user_id;
        $data['charge_by_tenant'] = $request->charge_by_tenant === 'yes' ? 1 : ($request->charge_by_tenant === 'no' ? 0 : null);
        $data['created_by']       = user()->id;

        if ($request->hasFile('image')) {
            $data['image'] = Files::upload($request->image, 'work_requests', 300);
        }
        $wr->update($data);

        if (!empty(request()->items_id) || !empty(request()->services_id)) {
            $items_ids     = request()->items_ids;
            $services_ids  = request()->services_ids;
            $items_id      = request()->items_id;
            $item_qty      = request()->items_qty;
            $item_harga    = request()->items_harga;
            $item_tax      = request()->items_tax;
            $services_id   = request()->services_id;
            $service_qty   = request()->services_qty;
            $service_harga = request()->services_harga;
            $service_tax   = request()->services_tax;

            if (!empty($items_ids)) {
                WorkItems::whereNotIn('id', $items_ids)
                    ->where('workrequest_id', $wr->id)
                    ->delete();
            }

            foreach ($item_qty as $key => $item_id) {
                $item_id = isset($items_ids[$key]) ? $items_ids[$key] : 0;

                try {
                    $item = WorkItems::findOrFail($item_id);
                } catch (Exception) {
                    $item = new WorkItems();
                }

                $item->company_id     = company()->id;
                $item->workrequest_id = $wr->id;
                $item->qty            = $item_qty[$key];
                $item->items_id       = $items_id[$key];
                $item->harga          = $item_harga[$key];
                $item->tax            = $item_tax[$key];
                $item->saveQuietly();
            }

            if (!empty($services_ids)) {
                WorkServices::whereNotIn('id', $services_ids)
                    ->where('workrequest_id', $wr->id)
                    ->delete();
            }

            foreach ($service_qty as $key => $service_id) {
                $service_id = isset($services_ids[$key]) ? $services_ids[$key] : 0;

                try {
                    $service = WorkServices::findOrFail($service_id);
                } catch (Exception) {
                    $service = new WorkServices();
                }

                $service->company_id     = company()->id;
                $service->workrequest_id = $wr->id;
                $service->qty            = $service_qty[$key];
                $service->services_id    = $services_id[$key];
                $service->harga          = $service_harga[$key];
                $service->tax            = $service_tax[$key];
                $service->saveQuietly();
            }
        }

        return Reply::successWithData(__('engineerings::messages.updateWR'), ['redirectUrl' => route('engineerings.show', [$wr->id])]);
    }

    public function show($id)
    {
        $this->permissions = user()->permission('view_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.wr.showWR');
        $this->wr        = WorkRequest::with('ticket', 'items.item', 'services.service', 'user', 'unit', 'house.area')->findOrFail($id);
        $this->employees = User::allEmployees(null, true);
        $this->items_arr = Item::all();
        $this->taxes     = Tax::all();
        $this->url       = asset_url('work_requests/' . $this->wr->image);
        return view('engineerings::workrequest.show', $this->data);
    }

    public function getItem($id)
    {
        $getItems = Item::where('id', $id)->get();
        return response()->json($getItems);
    }

    public function getService($id)
    {
        $getServices = Services::where('id', $id)->get();
        return response()->json($getServices);
    }
}

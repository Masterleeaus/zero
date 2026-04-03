<?php

namespace Modules\Engineerings\Http\Controllers;

use Carbon\Carbon;
use App\Helper\Files;
use App\Helper\Reply;
use Modules\Engineerings\Entities\Meter;
use App\Http\Controllers\AccountBaseController;
use Modules\Engineerings\Http\Requests\StoreMeter;
use Modules\Engineerings\DataTables\MeterDataTable;
use Modules\Units\Entities\Unit;

class MeterController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'engineerings::modules.meter';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('engineerings', $this->user->modules));
            return $next($request);
        });
    }

    public function index(MeterDataTable $dataTable)
    {
        $this->permissions = user()->permission('view_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->wo         = Meter::all();
        $this->totalUnits = count($this->wo);
        return $dataTable->render('engineerings::meters.index', $this->data);
    }

    public function create()
    {
        $this->permissions = user()->permission('add_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.meter.addMeter');
        $this->unit      = Unit::all();

        if (request()->ajax()) {
            $html = view('engineerings::meters.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'engineerings::meters.ajax.create';
        return view('engineerings::meters.create', $this->data);
    }

    public function store(StoreMeter $request)
    {
        $date = Carbon::createFromFormat($this->company->date_format, $request->input('billing_date'));
        $date = $date->addMonth()->startOfMonth();

        $data['unit_id']      = $request->unit_id;
        $data['end_meter']    = $request->end_meter;
        $data['type_bill']    = $request->type_bill;
        $data['billing_date'] = $date->format('Y-m-d');

        if ($request->hasFile('image')) {
            $data['image'] = Files::upload($request->image, 'meter-files', 300);
        }
        $meters = Meter::create($data);
        return Reply::successWithData(__('engineerings::messages.addMeter'), ['redirectUrl' => route('meter.index')]);
    }

    public function destroy($id)
    {
        $this->permissions = user()->permission('delete_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $project = Meter::findOrFail($id);
        Files::deleteDirectory(Meter::FILE_PATH . '/' . $id);
        $project->forceDelete();
        return Reply::success(__('engineerings::messages.deleteMeter'));
    }

    public function edit($id)
    {
        $this->permissions = user()->permission('edit_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.meter.editMeter');
        $this->unit      = Unit::all();
        $this->meter     = Meter::findOrFail($id);
        return view('engineerings::meters.ajax.edit', $this->data);
    }

    public function update(StoreMeter $request, $id)
    {
        $meter                = Meter::findOrFail($id);
        $data['unit_id']      = $request->unit_id;
        $data['end_meter']    = $request->end_meter;
        $data['type_bill']    = $request->type_bill;
        $data['billing_date'] = Carbon::createFromFormat($this->company->date_format, $request->input('billing_date'))->format('Y-m-d');

        if ($request->hasFile('image')) {
            $data['image'] = Files::upload($request->image, 'meter-files', 300);
        }
        $meter->update($data);
        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('meter.index');
        }

        return Reply::successWithData(__('engineerings::messages.updateMeter'), ['redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $this->permissions = user()->permission('view_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.meter.showMeter');
        $this->meter     = Meter::with('unit')->findOrFail($id);
        return view('engineerings::meters.show', $this->data);
    }

    public function scan()
    {
        return view('engineerings::meters.scan-modal');
    }
}

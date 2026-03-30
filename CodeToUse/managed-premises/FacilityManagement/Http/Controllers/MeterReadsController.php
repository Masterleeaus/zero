<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\MeterRead;
use Modules\FacilityManagement\Http\Requests\MeterReadRequest;

class MeterReadsController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\MeterRead::class, 'meter_read'); }

{
  public function index(){ $items = MeterRead::query()->paginate(20); return view('facility::reads.index', compact('items')); }
  public function create(){ return view('facility::reads.create'); }
  public function store(MeterReadRequest $r){ $item = MeterRead::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = MeterRead::findOrFail($id); return view('facility::reads.show', compact('item')); }
  public function edit($id){ $item = MeterRead::findOrFail($id); return view('facility::reads.edit', compact('item')); }
  public function update(MeterReadRequest $r,$id){ $item = MeterRead::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = MeterRead::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

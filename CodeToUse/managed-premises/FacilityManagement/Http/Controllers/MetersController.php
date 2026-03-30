<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\Meter;
use Modules\FacilityManagement\Http\Requests\MeterRequest;

class MetersController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\Meter::class, 'meter'); }

{
  public function index(){ $items = Meter::query()->paginate(20); return view('facility::meters.index', compact('items')); }
  public function create(){ return view('facility::meters.create'); }
  public function store(MeterRequest $r){ $item = Meter::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = Meter::findOrFail($id); return view('facility::meters.show', compact('item')); }
  public function edit($id){ $item = Meter::findOrFail($id); return view('facility::meters.edit', compact('item')); }
  public function update(MeterRequest $r,$id){ $item = Meter::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = Meter::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

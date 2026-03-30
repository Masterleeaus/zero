<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\UnitType;
use Modules\FacilityManagement\Http\Requests\UnitTypeRequest;

class UnitTypesController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\UnitType::class, 'unit_type'); }

{
  public function index(){ $items = UnitType::query()->paginate(20); return view('facility::unittypes.index', compact('items')); }
  public function create(){ return view('facility::unittypes.create'); }
  public function store(UnitTypeRequest $r){ $item = UnitType::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = UnitType::findOrFail($id); return view('facility::unittypes.show', compact('item')); }
  public function edit($id){ $item = UnitType::findOrFail($id); return view('facility::unittypes.edit', compact('item')); }
  public function update(UnitTypeRequest $r,$id){ $item = UnitType::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = UnitType::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

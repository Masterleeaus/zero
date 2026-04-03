<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\Unit;
use Modules\FacilityManagement\Http\Requests\UnitRequest;

class UnitsController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\Unit::class, 'unit'); }

{
  public function index(){ $items = Unit::query()->paginate(20); return view('facility::units.index', compact('items')); }
  public function create(){ return view('facility::units.create'); }
  public function store(UnitRequest $r){ $item = Unit::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = Unit::findOrFail($id); return view('facility::units.show', compact('item')); }
  public function edit($id){ $item = Unit::findOrFail($id); return view('facility::units.edit', compact('item')); }
  public function update(UnitRequest $r,$id){ $item = Unit::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = Unit::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

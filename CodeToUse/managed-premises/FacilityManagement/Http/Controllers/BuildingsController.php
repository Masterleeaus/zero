<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\Building;
use Modules\FacilityManagement\Http\Requests\BuildingRequest;

class BuildingsController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\Building::class, 'building'); }

{
  public function index(){ $items = Building::query()->paginate(20); return view('facility::buildings.index', compact('items')); }
  public function create(){ return view('facility::buildings.create'); }
  public function store(BuildingRequest $r){ $item = Building::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = Building::findOrFail($id); return view('facility::buildings.show', compact('item')); }
  public function edit($id){ $item = Building::findOrFail($id); return view('facility::buildings.edit', compact('item')); }
  public function update(BuildingRequest $r,$id){ $item = Building::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = Building::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\Occupancy;
use Modules\FacilityManagement\Http\Requests\OccupancyRequest;

class OccupancyController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\Occupancy::class, 'occupancy'); }

{
  public function index(){ $items = Occupancy::query()->paginate(20); return view('facility::occupancy.index', compact('items')); }
  public function create(){ return view('facility::occupancy.create'); }
  public function store(OccupancyRequest $r){ $item = Occupancy::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = Occupancy::findOrFail($id); return view('facility::occupancy.show', compact('item')); }
  public function edit($id){ $item = Occupancy::findOrFail($id); return view('facility::occupancy.edit', compact('item')); }
  public function update(OccupancyRequest $r,$id){ $item = Occupancy::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = Occupancy::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

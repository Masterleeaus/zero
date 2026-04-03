<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\Inspection;
use Modules\FacilityManagement\Http\Requests\InspectionRequest;

class InspectionsController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\Inspection::class, 'inspection'); }

{
  public function index(){ $items = Inspection::query()->paginate(20); return view('facility::inspections.index', compact('items')); }
  public function create(){ return view('facility::inspections.create'); }
  public function store(InspectionRequest $r){ $item = Inspection::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = Inspection::findOrFail($id); return view('facility::inspections.show', compact('item')); }
  public function edit($id){ $item = Inspection::findOrFail($id); return view('facility::inspections.edit', compact('item')); }
  public function update(InspectionRequest $r,$id){ $item = Inspection::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = Inspection::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

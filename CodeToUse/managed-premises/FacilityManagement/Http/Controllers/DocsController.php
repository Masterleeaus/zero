<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\Doc;
use Modules\FacilityManagement\Http\Requests\DocRequest;

class DocsController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\Doc::class, 'doc'); }

{
  public function index(){ $items = Doc::query()->paginate(20); return view('facility::docs.index', compact('items')); }
  public function create(){ return view('facility::docs.create'); }
  public function store(DocRequest $r){ $item = Doc::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = Doc::findOrFail($id); return view('facility::docs.show', compact('item')); }
  public function edit($id){ $item = Doc::findOrFail($id); return view('facility::docs.edit', compact('item')); }
  public function update(DocRequest $r,$id){ $item = Doc::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = Doc::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

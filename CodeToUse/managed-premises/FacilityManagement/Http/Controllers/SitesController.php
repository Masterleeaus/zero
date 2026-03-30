<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\Site;
use Modules\FacilityManagement\Http\Requests\SiteRequest;

class SitesController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\Site::class, 'site'); }

{
  public function index(){ $items = Site::query()->paginate(20); return view('facility::sites.index', compact('items')); }
  public function create(){ return view('facility::sites.create'); }
  public function store(SiteRequest $r){ $item = Site::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = Site::findOrFail($id); return view('facility::sites.show', compact('item')); }
  public function edit($id){ $item = Site::findOrFail($id); return view('facility::sites.edit', compact('item')); }
  public function update(SiteRequest $r,$id){ $item = Site::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = Site::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

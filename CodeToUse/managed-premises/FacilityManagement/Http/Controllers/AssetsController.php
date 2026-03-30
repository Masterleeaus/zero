<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Entities\Asset;
use Modules\FacilityManagement\Http\Requests\AssetRequest;

class AssetsController extends Controller
{
  public function __construct(){ $this->authorizeResource(\Modules\FacilityManagement\Entities\Asset::class, 'asset'); }

{
  public function index(){ $items = Asset::query()->paginate(20); return view('facility::assets.index', compact('items')); }
  public function create(){ return view('facility::assets.create'); }
  public function store(AssetRequest $r){ $item = Asset::create($r->validated()); return redirect()->back()->with('status','Created'); }
  public function show($id){ $item = Asset::findOrFail($id); return view('facility::assets.show', compact('item')); }
  public function edit($id){ $item = Asset::findOrFail($id); return view('facility::assets.edit', compact('item')); }
  public function update(AssetRequest $r,$id){ $item = Asset::findOrFail($id); $item->update($r->validated()); return redirect()->back()->with('status','Updated'); }
  public function destroy($id){ $item = Asset::findOrFail($id); $item->delete(); return redirect()->back()->with('status','Deleted'); }
}

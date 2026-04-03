<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\Entity;

class EntityController extends Controller
{
    public function index(){ $entities = Entity::orderBy('id','desc')->paginate(20); return view('titantalk::entities.index', compact('entities')); }
    public function create(){ return view('titantalk::entities.create'); }
    public function store(Request $r){
        $data = $r->validate(['name'=>'required|string|max:255','values'=>'nullable|string']);
        $payload = ['name'=>$data['name'], 'values'=> $data['values']? array_map('trim', explode(',', $data['values'])):[]];
        $e = Entity::create($payload);
        return redirect()->route('titantalk.entities.edit', $e->id)->with('status','Entity created.');
    }
    public function edit($id){ $entity = Entity::findOrFail($id); return view('titantalk::entities.edit', compact('entity')); }
    public function update(Request $r, $id){
        $entity = Entity::findOrFail($id);
        $data = $r->validate(['name'=>'required|string|max:255','values'=>'nullable|string']);
        $entity->update(['name'=>$data['name'],'values'=>$data['values']? array_map('trim', explode(',', $data['values'])):[]]);
        return back()->with('status','Entity updated.');
    }
    public function destroy($id){ Entity::findOrFail($id)->delete(); return redirect()->route('titantalk.entities.index')->with('status','Deleted.'); }
    public function export(){
        $json = Entity::all()->toJson(JSON_PRETTY_PRINT);
        return response($json,200,['Content-Type'=>'application/json','Content-Disposition'=>'attachment; filename="aiconverse_entities.json"']);
    }
    public function import(Request $r){
        $r->validate(['file'=>'required|file']);
        $data = json_decode(file_get_contents($r->file('file')->getRealPath()), true) ?: [];
        foreach($data as $row){ Entity::updateOrCreate(['name'=>$row['name'] ?? 'unnamed'], ['values'=>$row['values'] ?? [], 'metadata'=>$row['metadata'] ?? null]); }
        return back()->with('status','Imported entities.');
    }
}

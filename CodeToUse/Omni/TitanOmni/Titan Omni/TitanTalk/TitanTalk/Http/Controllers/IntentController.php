<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\Intent;

class IntentController extends Controller
{
    public function index(){ $intents = Intent::orderBy('id','desc')->paginate(20); return view('titantalk::intents.index', compact('intents')); }
    public function create(){ return view('titantalk::intents.create'); }
    public function store(Request $r){
        $data = $r->validate(['name'=>'required|string|max:255','description'=>'nullable|string']);
        $i = Intent::create($data);
        return redirect()->route('titantalk.intents.edit', $i->id)->with('status','Intent created.');
    }
    public function edit($id){ $intent = Intent::findOrFail($id); return view('titantalk::intents.edit', compact('intent')); }
    public function update(Request $r, $id){
        $intent = Intent::findOrFail($id);
        $data = $r->validate(['name'=>'required|string|max:255','description'=>'nullable|string']);
        $intent->update($data);
        return back()->with('status','Intent updated.');
    }
    public function destroy($id){ Intent::findOrFail($id)->delete(); return redirect()->route('titantalk.intents.index')->with('status','Deleted.'); }
    public function export(){
        $json = Intent::all()->toJson(JSON_PRETTY_PRINT);
        return response($json,200,['Content-Type'=>'application/json','Content-Disposition'=>'attachment; filename="aiconverse_intents.json"']);
    }
    public function import(Request $r){
        $r->validate(['file'=>'required|file']);
        $data = json_decode(file_get_contents($r->file('file')->getRealPath()), true) ?: [];
        foreach($data as $row){ Intent::updateOrCreate(['name'=>$row['name'] ?? 'unnamed'], ['description'=>$row['description'] ?? null, 'metadata'=>$row['metadata'] ?? null]); }
        return back()->with('status','Imported intents.');
    }
}

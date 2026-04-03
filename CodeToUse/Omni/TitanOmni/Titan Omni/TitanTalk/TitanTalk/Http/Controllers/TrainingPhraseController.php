<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\TrainingPhrase;
use Modules\TitanTalk\Models\Intent;

class TrainingPhraseController extends Controller
{
    public function index(){
        $phrases = TrainingPhrase::with('intent')->orderBy('id','desc')->paginate(25);
        $intents = Intent::orderBy('name')->get();
        return view('titantalk::training.index', compact('phrases','intents'));
    }
    public function store(Request $r){
        $data = $r->validate(['intent_id'=>'required|integer','text'=>'required|string']);
        TrainingPhrase::create($data);
        return back()->with('status','Added training phrase.');
    }
    public function destroy($id){
        TrainingPhrase::findOrFail($id)->delete();
        return back()->with('status','Deleted.');
    }
    public function export(){
        $json = TrainingPhrase::all()->toJson(JSON_PRETTY_PRINT);
        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="aiconverse_training_phrases.json"',
        ]);
    }
}

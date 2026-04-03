<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\Channel;

class ChannelController extends Controller
{
    public function index(){
        $channels = Channel::orderBy('id','desc')->paginate(20);
        return view('titantalk::channels.index', compact('channels'));
    }
    public function create(){ return view('titantalk::channels.create'); }
    public function store(Request $r){
        $data = $r->validate([
            'name'=>'required|string|max:100',
            'driver'=>'required|string|max:50',
            'enabled'=>'nullable|boolean'
        ]);
        $cfg = $r->input('config', []);
        if (is_string($cfg)) { $cfg = json_decode($cfg, true) ?: []; }
        $data['config'] = $cfg;
        $data['enabled'] = (bool)($data['enabled'] ?? true);
        $ch = Channel::create($data);
        return redirect()->route('titantalk.channels.edit', $ch->id)->with('status','Channel created.');
    }
    public function edit($id){
        $channel = Channel::findOrFail($id);
        return view('titantalk::channels.edit', compact('channel'));
    }
    public function update(Request $r, $id){
        $channel = Channel::findOrFail($id);
        $data = $r->validate([
            'name'=>'required|string|max:100',
            'driver'=>'required|string|max:50',
            'enabled'=>'nullable|boolean'
        ]);
        $cfg = $r->input('config', []);
        if (is_string($cfg)) { $cfg = json_decode($cfg, true) ?: []; }
        $data['config'] = $cfg;
        $data['enabled'] = (bool)($data['enabled'] ?? true);
        $channel->update($data);
        return back()->with('status','Channel updated.');
    }
    public function destroy($id){
        Channel::findOrFail($id)->delete();
        return redirect()->route('titantalk.channels.index')->with('status','Deleted.');
    }
}

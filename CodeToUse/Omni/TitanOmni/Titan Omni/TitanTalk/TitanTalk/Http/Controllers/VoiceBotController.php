<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\VoiceBot;

class VoiceBotController extends Controller
{
    public function index()
    {
        $bots = VoiceBot::orderBy('created_at', 'desc')->paginate(20);

        return view('titantalk::voice.index', compact('bots'));
    }

    public function create()
    {
        return view('titantalk::voice.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'provider'    => 'nullable|string|max:191',
            'external_id' => 'nullable|string|max:191',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['provider']  = $data['provider'] ?? 'elevenlabs';
        $data['is_active'] = $request->boolean('is_active', true);

        VoiceBot::create($data);

        return redirect()->route('titantalk.voice-bots.index')->with('success', 'Voice bot created.');
    }

    public function edit(VoiceBot $voice_bot)
    {
        return view('titantalk::voice.edit', ['bot' => $voice_bot]);
    }

    public function update(Request $request, VoiceBot $voice_bot)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'provider'    => 'nullable|string|max:191',
            'external_id' => 'nullable|string|max:191',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['provider']  = $data['provider'] ?? $voice_bot->provider;
        $data['is_active'] = $request->boolean('is_active', $voice_bot->is_active);

        $voice_bot->update($data);

        return redirect()->route('titantalk.voice-bots.index')->with('success', 'Voice bot updated.');
    }

    public function destroy(VoiceBot $voice_bot)
    {
        $voice_bot->delete();

        return redirect()->route('titantalk.voice-bots.index')->with('success', 'Voice bot deleted.');
    }
}

<?php

namespace Extensions\TitanHello\Controllers;

use Extensions\TitanHello\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function index()
    {
        return view('titan-hello::admin.settings', [
            'settings' => $this->settings->all(),
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'twilio.account_sid' => 'nullable|string',
            'twilio.auth_token' => 'nullable|string',
            'twilio.default_number' => 'nullable|string',
            'twilio.sms_from_number' => 'nullable|string',
            'elevenlabs.api_key' => 'nullable|string',
            'elevenlabs.user_input_audio_format' => 'nullable|string',
            'elevenlabs.agent_output_audio_format' => 'nullable|string',
            'bridge.public_ws_url' => 'nullable|string',
            'bridge.host' => 'nullable|string',
            'bridge.port' => 'nullable|integer|min:1|max:65535',
            'routing.forward_number' => 'nullable|string',
            'routing.timezone' => 'nullable|string',
            'routing.business_hours' => 'nullable|array',
            'routing.after_hours_mode' => 'nullable|string|in:take_message,forward',
            'recording.enabled' => 'nullable|boolean',
            'recording.consent_message' => 'nullable|string',
            'followup.sms_enabled' => 'nullable|boolean',
            'followup.sms_template' => 'nullable|string',
        ]);

        // Checkbox normalization
        $data['recording']['enabled'] = (bool)($data['recording']['enabled'] ?? false);
        $data['followup']['sms_enabled'] = (bool)($data['followup']['sms_enabled'] ?? false);

        // Store as dot-keys to keep storage simple.
        $this->settings->setMany($this->flatten($data));

        return back()->with('success', __('Titan Hello settings saved.'));
    }

    private function flatten(array $arr, string $prefix = ''): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $key = $prefix === '' ? (string) $k : $prefix . '.' . $k;
            if (is_array($v)) {
                $out = array_merge($out, $this->flatten($v, $key));
            } else {
                $out[$key] = $v;
            }
        }
        return $out;
    }
}

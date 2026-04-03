@extends('panel.layout.app')

@section('title', __('Titan Hello Settings'))

@section('content')
    <div class="py-10">
        <div class="max-w-3xl">
            @if(session('success'))
                <div class="alert alert-success mb-6">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.extensions.titan-hello.settings.save') }}">
                @csrf

                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Twilio') }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">{{ __('Account SID') }}</label>
                            <input class="form-control" name="twilio[account_sid]" value="{{ old('twilio.account_sid', $settings['twilio.account_sid'] ?? '') }}" />
                            @error('twilio.account_sid')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('Auth Token') }}</label>
                            <input class="form-control" type="password" name="twilio[auth_token]" value="{{ old('twilio.auth_token', $settings['twilio.auth_token'] ?? '') }}" />
                            @error('twilio.auth_token')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('Default Phone Number') }}</label>
                            <input class="form-control" name="twilio[default_number]" placeholder="+614xxxxxxxx" value="{{ old('twilio.default_number', $settings['twilio.default_number'] ?? '') }}" />
                            @error('twilio.default_number')<div class="text-danger mt-1">{{ $message }}</div>
                        <div>
                            <label class="form-label">{{ __('SMS From Number (optional)') }}</label>
                            <input class="form-control" name="twilio[sms_from_number]" value="{{ old('twilio.sms_from_number', $settings['twilio.sms_from_number'] ?? '') }}" />
                            <small class="text-muted">{{ __('If empty, Titan Hello will use Default Number for SMS.') }}</small>
                        </div>
@enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('ElevenLabs (Conversational AI)') }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">{{ __('API Key (optional if already configured globally)') }}</label>
                            <input class="form-control" type="password" name="elevenlabs[api_key]" value="{{ old('elevenlabs.api_key', $settings['elevenlabs.api_key'] ?? '') }}" />
                            <small class="form-hint">{{ __('Used by the WS bridge to obtain signed URLs for private agents.') }}</small>
                            @error('elevenlabs.api_key')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">{{ __('User input audio format') }}</label>
                                <input class="form-control" name="elevenlabs[user_input_audio_format]" placeholder="ulaw_8000" value="{{ old('elevenlabs.user_input_audio_format', $settings['elevenlabs.user_input_audio_format'] ?? 'ulaw_8000') }}" />
                                <small class="form-hint">{{ __('For Twilio Media Streams, ulaw_8000 is usually correct.') }}</small>
                                @error('elevenlabs.user_input_audio_format')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label">{{ __('Agent output audio format') }}</label>
                                <input class="form-control" name="elevenlabs[agent_output_audio_format]" placeholder="ulaw_8000" value="{{ old('elevenlabs.agent_output_audio_format', $settings['elevenlabs.agent_output_audio_format'] ?? 'ulaw_8000') }}" />
                                <small class="form-hint">{{ __('Must match what Twilio expects (μ-law 8k for lowest latency).') }}</small>
                                @error('elevenlabs.agent_output_audio_format')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('WebSocket Bridge') }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">{{ __('Public WS URL (Twilio connects here)') }}</label>
                            <input class="form-control" name="bridge[public_ws_url]" placeholder="wss://yourdomain.com/api/titan-hello/twilio/voice/stream" value="{{ old('bridge.public_ws_url', $settings['bridge.public_ws_url'] ?? '') }}" />
                            <small class="form-hint">{{ __('If blank, Titan Hello will derive it from APP_URL.') }}</small>
                            @error('bridge.public_ws_url')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">{{ __('Daemon bind host') }}</label>
                                <input class="form-control" name="bridge[host]" placeholder="127.0.0.1" value="{{ old('bridge.host', $settings['bridge.host'] ?? '127.0.0.1') }}" />
                                @error('bridge.host')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label">{{ __('Daemon bind port') }}</label>
                                <input class="form-control" name="bridge[port]" placeholder="8081" value="{{ old('bridge.port', $settings['bridge.port'] ?? 8081) }}" />
                                @error('bridge.port')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Routing') }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">{{ __('Timezone') }}</label>
                            <input class="form-control" name="routing[timezone]" placeholder="Australia/Melbourne" value="{{ old('routing.timezone', $settings['routing.timezone'] ?? 'Australia/Melbourne') }}" />
                            @error('routing.timezone')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('After-hours mode') }}</label>
                            <select class="form-select" name="routing[after_hours_mode]">
                                @php($mode = old('routing.after_hours_mode', $settings['routing.after_hours_mode'] ?? 'take_message'))
                                <option value="take_message" @selected($mode === 'take_message')>{{ __('AI takes a message') }}</option>
                                <option value="forward" @selected($mode === 'forward')>{{ __('Forward to a number') }}</option>
                            </select>
                            @error('routing.after_hours_mode')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('Forward number (optional)') }}</label>
                            <input class="form-control" name="routing[forward_number]" placeholder="+614xxxxxxxx" value="{{ old('routing.forward_number', $settings['routing.forward_number'] ?? '') }}" />
                            @error('routing.forward_number')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            <small class="form-hint">{{ __('Used when after-hours mode is set to Forward, or as an AI-failover.') }}</small>
                        </div>
                    </div>
                </div>

                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Recording & Consent') }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="form-check">
                            @php($recEnabled = (bool)old('recording.enabled', $settings['recording.enabled'] ?? false))
                            <input class="form-check-input" type="checkbox" id="rec_enabled" name="recording[enabled]" value="1" @checked($recEnabled)>
                            <label class="form-check-label" for="rec_enabled">{{ __('Enable call recording (if supported by provider settings)') }}</label>
                        </div>
                        <div>
                            <label class="form-label">{{ __('Consent message (spoken)') }}</label>
                            <textarea class="form-control" rows="3" name="recording[consent_message]">{{ old('recording.consent_message', $settings['recording.consent_message'] ?? __('This call may be recorded for quality and proof of work.')) }}</textarea>
                            @error('recording.consent_message')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Follow-up') }}</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="followup[sms_enabled]" value="1"
                                {{ old('followup.sms_enabled', $settings['followup.sms_enabled'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Send SMS follow-up on missed/after-hours calls') }}</label>
                        </div>
                        <div>
                            <label class="form-label">{{ __('SMS Template') }}</label>
                            <textarea class="form-control" rows="3" name="followup[sms_template]">{{ old('followup.sms_template', $settings['followup.sms_template'] ?? '') }}</textarea>
                            <small class="text-muted">{{ __('Keep it short. This is sent after missed calls or after-hours voicemail.') }}</small>
                        </div>
                    </div>
                </div>

<div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">{{ __('Save Settings') }}</button>
                    <a href="{{ route('admin.extensions.titan-hello.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

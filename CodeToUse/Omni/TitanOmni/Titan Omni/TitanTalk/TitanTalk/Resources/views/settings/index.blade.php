@extends('titantalk::layouts.master')

@section('title', 'Titan Talk – Channel Settings')

@section('content')
    <div class="row mb-3">
        <div class="col-md-12">
            <h3 class="page-title">Titan Talk – Channel Settings</h3>
            <p class="text-muted mb-0">
                Configure SMS and Email credentials used by Titan Talk for unified inbox and replies.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('titantalk.settings.save') }}">
        @csrf

        <div class="row">
            {{-- SMS Settings --}}
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        SMS Channel
                    </div>
                    <div class="card-body">
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="sms_enabled" name="sms_enabled"
                                   value="1" {{ $sms->enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="sms_enabled">Enable SMS channel</label>
                        </div>

                        <div class="form-group mt-2">
                            <label>Provider</label>
                            <input type="text" name="sms_provider" class="form-control"
                                   placeholder="e.g. twilio, nexmo"
                                   value="{{ $sms->config['provider'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>From number</label>
                            <input type="text" name="sms_from" class="form-control"
                                   placeholder="e.g. +61XXXXXXXXX"
                                   value="{{ $sms->config['from'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>API key</label>
                            <input type="text" name="sms_api_key" class="form-control"
                                   value="{{ $sms->config['api_key'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>API secret / token</label>
                            <input type="text" name="sms_api_secret" class="form-control"
                                   value="{{ $sms->config['api_secret'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Extra config (JSON or notes)</label>
                            <textarea name="sms_extra" class="form-control" rows="2">{{ $sms->config['extra'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Email Settings --}}
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        Email Channel
                    </div>
                    <div class="card-body">
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="email_enabled" name="email_enabled"
                                   value="1" {{ $email->enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_enabled">Enable Email channel</label>
                        </div>

                        <div class="form-group mt-2">
                            <label>Host</label>
                            <input type="text" name="email_host" class="form-control"
                                   placeholder="e.g. smtp.mailgun.org"
                                   value="{{ $email->config['host'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Port</label>
                            <input type="number" name="email_port" class="form-control"
                                   placeholder="587"
                                   value="{{ $email->config['port'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Encryption</label>
                            <input type="text" name="email_encryption" class="form-control"
                                   placeholder="tls or ssl"
                                   value="{{ $email->config['encryption'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Username</label>
                            <input type="text" name="email_username" class="form-control"
                                   value="{{ $email->config['username'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>Password</label>
                            <input type="password" name="email_password" class="form-control"
                                   value="{{ $email->config['password'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>From name</label>
                            <input type="text" name="email_from_name" class="form-control"
                                   placeholder="e.g. Titan Talk"
                                   value="{{ $email->config['from_name'] ?? '' }}">
                        </div>

                        <div class="form-group mt-2">
                            <label>From address</label>
                            <input type="email" name="email_from_address" class="form-control"
                                   placeholder="e.g. no-reply@yourdomain.com"
                                   value="{{ $email->config['from_address'] ?? '' }}">
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-primary">Save Settings</button>
    </form>
@endsection

@extends('titantalk::layouts.master')

@section('title', 'Create Voice Bot – Titan Talk')

@section('content')
    <div class="row mb-3">
        <div class="col-md-6">
            <h3 class="page-title">Create Voice Bot</h3>
        </div>
        <div class="col-md-6 text-right">
            <a class="btn btn-secondary" href="{{ route('titantalk.voice-bots.index') }}">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('titantalk.voice-bots.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="form-group mt-2">
                    <label>Provider</label>
                    <input type="text" name="provider" class="form-control" value="{{ old('provider', 'elevenlabs') }}">
                    <small class="text-muted">e.g. elevenlabs, retell, twilio</small>
                </div>

                <div class="form-group mt-2">
                    <label>External ID</label>
                    <input type="text" name="external_id" class="form-control" value="{{ old('external_id') }}">
                    <small class="text-muted">Voice / agent ID from your provider.</small>
                </div>

                <div class="form-check mt-3">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save</button>
            </form>
        </div>
    </div>
@endsection

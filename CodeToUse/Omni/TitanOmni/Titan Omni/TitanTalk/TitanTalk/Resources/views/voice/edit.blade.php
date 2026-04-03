@extends('titantalk::layouts.master')

@section('title', 'Edit Voice Bot – Titan Talk')

@section('content')
    <div class="row mb-3">
        <div class="col-md-6">
            <h3 class="page-title">Edit Voice Bot</h3>
        </div>
        <div class="col-md-6 text-right">
            <a class="btn btn-secondary" href="{{ route('titantalk.voice-bots.index') }}">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('titantalk.voice-bots.update', $bot) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $bot->name) }}" required>
                </div>

                <div class="form-group mt-2">
                    <label>Provider</label>
                    <input type="text" name="provider" class="form-control" value="{{ old('provider', $bot->provider) }}">
                </div>

                <div class="form-group mt-2">
                    <label>External ID</label>
                    <input type="text" name="external_id" class="form-control" value="{{ old('external_id', $bot->external_id) }}">
                </div>

                <div class="form-check mt-3">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ $bot->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save</button>
            </form>
        </div>
    </div>
@endsection

@extends('titantalk::layouts.app')

@section('titantalk-content')
<div class="card">
    <div class="card-body">
        <h4 class="mb-3">{{ isset($intent) ? 'Edit Intent' : 'Create Intent' }}</h4>

        <form method="POST" action="{{ isset($intent) ? route('titantalk.intents.update',$intent->id ?? $intent) : route('titantalk.intents.store') }}">
            @csrf
            @if(isset($intent)) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name', $intent->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4">{{ old('description', $intent->description ?? '') }}</textarea>
            </div>

            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection

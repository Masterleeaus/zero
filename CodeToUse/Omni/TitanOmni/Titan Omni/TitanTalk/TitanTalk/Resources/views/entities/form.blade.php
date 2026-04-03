@extends('titantalk::layouts.app')

@section('titantalk-content')
<div class="card">
    <div class="card-body">
        <h4 class="mb-3">{{ isset($entity) ? 'Edit Entity' : 'Create Entity' }}</h4>

        <form method="POST" action="{{ isset($entity) ? route('titantalk.entities.update',$entity->id ?? $entity) : route('titantalk.entities.store') }}">
            @csrf
            @if(isset($entity)) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name', $entity->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Type</label>
                <input class="form-control" name="type" value="{{ old('type', $entity->type ?? '') }}">
            </div>

            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection

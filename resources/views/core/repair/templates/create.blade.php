@extends('default.panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">New Repair Template</h1>

    <form action="{{ route('repair.templates.store') }}" method="POST">
        @csrf
        @include('core.repair.templates._form')
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Create Template</button>
            <a href="{{ route('repair.templates.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection

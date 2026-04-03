@extends('default.panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Edit Template: {{ $template->name }}</h1>

    <form action="{{ route('repair.templates.update', $template) }}" method="POST">
        @csrf
        @method('PUT')
        @include('core.repair.templates._form')
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Update Template</button>
            <a href="{{ route('repair.templates.show', $template) }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection

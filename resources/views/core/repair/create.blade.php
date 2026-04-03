@extends('default.panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">New Repair Order</h1>

    <form action="{{ route('repair.orders.store') }}" method="POST">
        @csrf
        @include('core.repair._form')
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Create Repair Order</button>
            <a href="{{ route('repair.orders.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection

@extends('default.panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Edit Repair {{ $repair->repair_number }}</h1>

    <form action="{{ route('repair.orders.update', $repair) }}" method="POST">
        @csrf
        @method('PUT')
        @include('core.repair._form')
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Update Repair Order</button>
            <a href="{{ route('repair.orders.show', $repair) }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection

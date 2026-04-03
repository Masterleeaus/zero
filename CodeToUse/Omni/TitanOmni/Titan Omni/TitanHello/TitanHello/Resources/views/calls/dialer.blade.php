@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Titan Hello – Dialer</h3>
        <a class="btn btn-outline-secondary" href="{{ route('titanhello.calls.index') }}">Back to inbox</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('titanhello.calls.dialer.call') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">To number</label>
                    <input type="text" name="to_number" class="form-control" placeholder="+61..." required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">From number (optional)</label>
                    <input type="text" name="from_number" class="form-control" placeholder="+61...">
                    <div class="form-text">If blank, uses provider default from number.</div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Call</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

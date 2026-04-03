@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Titan Hello – Call Inbox</h3>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('titanhello.dashboard') }}">Home</a>
            <a class="btn btn-primary" href="{{ route('titanhello.calls.dialer') }}">Dialer</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            @include('titanhello::calls.partials.filters')
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Assignment</th>
                        <th>Last update</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($calls as $c)
                    @include('titanhello::calls.partials.row', ['c' => $c])
                @empty
                    <tr><td colspan="7" class="text-muted p-4">No calls match your filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $calls->links() }}
        </div>
    </div>
</div>
@endsection

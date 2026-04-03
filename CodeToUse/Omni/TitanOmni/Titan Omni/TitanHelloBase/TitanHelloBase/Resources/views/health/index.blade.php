@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Titan Hello – Health</h3>
        <a class="btn btn-outline-secondary" href="{{ route('titanhello.dashboard') }}">Back</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                    <tr><th>Check</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($checks as $k => $v)
                        <tr>
                            <td>{{ $k }}</td>
                            <td>
                                @if($v)
                                    <span class="badge bg-success">OK</span>
                                @else
                                    <span class="badge bg-danger">Missing</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('performance::layouts.master')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">{{ __('performance::job_performance.job_performance') }} #{{ $snapshot->id }}</h4>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3"><div class="card-body">
                <h6>Scores</h6>
                <ul class="list-unstyled mb-0">
                    <li>Overall: <strong>{{ $snapshot->overall_score }}</strong></li>
                    <li>Quality: {{ $snapshot->quality_score }}</li>
                    <li>Safety: {{ $snapshot->safety_score }}</li>
                    <li>Timeliness: {{ $snapshot->timeliness_score }}</li>
                    <li>Documentation: {{ $snapshot->documentation_score }}</li>
                </ul>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-3"><div class="card-body">
                <h6>Actions</h6>
                <form method="POST" action="{{ route('job-performance.rescore', $snapshot->id) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">{{ __('performance::job_performance.rescore') }}</button>
                </form>
                <form method="POST" action="{{ route('job-performance.signoff', $snapshot->id) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-success">{{ __('performance::job_performance.sign_off') }}</button>
                </form>
                <div class="mt-2">
                    Status: <strong>{{ $snapshot->status }}</strong>
                    @if($snapshot->signed_off_at)
                        <div class="small text-muted">Signed off: {{ $snapshot->signed_off_at }}</div>
                    @endif
                </div>
            </div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3"><div class="card-body">
                <h6>{{ __('performance::job_quality.quality_metrics') }}</h6>
                <ul class="mb-0">
                    @foreach($snapshot->qualityMetrics as $m)
                        <li>{{ $m->label }}: {{ $m->value }} {{ $m->unit }}</li>
                    @endforeach
                </ul>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-3"><div class="card-body">
                <h6>{{ __('performance::job_safety.safety_checks') }}</h6>
                <ul class="mb-0">
                    @foreach($snapshot->safetyMetrics as $m)
                        <li>{{ $m->label }}: {{ is_null($m->passed) ? '-' : ($m->passed ? 'PASS' : 'FAIL') }}</li>
                    @endforeach
                </ul>
            </div></div>
        </div>
    </div>
</div>
@endsection

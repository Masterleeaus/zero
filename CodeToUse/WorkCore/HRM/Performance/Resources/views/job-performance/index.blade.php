@extends('performance::layouts.master')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">{{ __('performance::job_performance.job_performance') }}</h4>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('performance::job_performance.overall_score') }}</th>
                            <th>{{ __('performance::job_performance.quality') }}</th>
                            <th>{{ __('performance::job_performance.safety') }}</th>
                            <th>{{ __('performance::job_performance.timeliness') }}</th>
                            <th>{{ __('performance::job_performance.documentation') }}</th>
                            <th>{{ __('performance::job_performance.callbacks') }}</th>
                            <th>{{ __('performance::job_performance.customer_rating') }}</th>
                            <th>{{ __('performance::job_performance.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($snapshots as $s)
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td>{{ $s->overall_score }}</td>
                            <td>{{ $s->quality_score }}</td>
                            <td>{{ $s->safety_score }}</td>
                            <td>{{ $s->timeliness_score }}</td>
                            <td>{{ $s->documentation_score }}</td>
                            <td>{{ $s->callback_count }}</td>
                            <td>{{ $s->customer_rating }}</td>
                            <td>{{ $s->status }}</td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('job-performance.show', $s->id) }}">View</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $snapshots->links() }}
        </div>
    </div>
</div>
@endsection

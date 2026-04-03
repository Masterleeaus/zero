@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.title') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <pre>{{ json_encode($timesheet ?? null, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
@endsection

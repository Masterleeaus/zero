@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.actions.add') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.actions.add') }}</h5>
                <a href="{{ route('timesheet.index') }}" class="btn btn-sm btn-light">{{ __('Timesheet::timesheet.actions.back') }}</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('timesheet.store') }}">
                    @include('timesheet::timesheet.partials.form')
                    <div class="mt-3">
                        <button class="btn btn-primary">{{ __('Timesheet::timesheet.actions.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('pageTitle', $mode === 'create' ? __('Create Workflow') : __('Edit Workflow'))

@section('content')
<div class="content-wrapper">
    <h1 class="h3 mb-3">{{ $mode === 'create' ? __('Create Workflow') : __('Edit Workflow') }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $mode === 'create' ? route('workflow.account.workflows.store') : route('workflow.account.workflows.update', $workflow->id) }}">
                @csrf
                @if($mode === 'edit') @method('PUT') @endif

                <div class="form-group">
                    <label>{{ __('Name') }}</label>
                    <input class="form-control" name="name" value="{{ old('name', $workflow->name) }}" required />
                </div>

                <div class="form-group">
                    <label>{{ __('Description') }}</label>
                    <textarea class="form-control" name="description">{{ old('description', $workflow->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label>{{ __('Trigger Event') }}</label>
                    <select class="form-control" name="trigger_event">
                        <option value="">{{ __('— Select —') }}</option>
                        @foreach($triggers as $t)
                            <option value="{{ $t['event'] }}" @selected(old('trigger_event', $workflow->trigger_event) === $t['event'])>
                                {{ $t['label'] }} ({{ $t['event'] }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('You can also type a custom event in DB later; this list is the recommended starter set.') }}</small>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $workflow->is_active) ? true : false) />
                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                </div>

                <button class="btn btn-primary" type="submit">{{ __('Save') }}</button>
                <a class="btn btn-light" href="{{ route('workflow.account.workflows.index') }}">{{ __('Back') }}</a>
            </form>

            <hr />

            <h5 class="mb-2">{{ __('Available Actions (step handlers)') }}</h5>
            <ul class="mb-0">
                @foreach($actions as $a)
                    <li><code>{{ $a['key'] }}</code> — {{ $a['label'] }} <span class="text-muted">({{ $a['handler'] }})</span></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection

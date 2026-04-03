@extends('layouts.app')
@section('content')
<div class="container py-4">
  <h1>Budget AI Settings</h1>
  <div class="card">
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">AI Enabled</dt><dd class="col-sm-9">{{ $status['enabled'] ? 'Yes' : 'No' }}</dd>
        <dt class="col-sm-3">Provider</dt><dd class="col-sm-9">{{ $status['provider'] }}</dd>
        <dt class="col-sm-3">Model</dt><dd class="col-sm-9">{{ $status['model'] }}</dd>
        <dt class="col-sm-3">Key Present</dt><dd class="col-sm-9">{{ $status['has_key'] ? 'Yes' : 'No' }}</dd>
        <dt class="col-sm-3">Max Months</dt><dd class="col-sm-9">{{ $status['max_months'] }}</dd>
        <dt class="col-sm-3">Safe Mode</dt><dd class="col-sm-9">{{ $status['safe_mode'] ? 'On' : 'Off' }}</dd>
      </dl>
      <a class="btn btn-outline-primary mt-3" href="{{ route('budgets.create') }}">Create with AI</a>
      <a class="btn btn-secondary mt-3" href="{{ route('budgets.index') }}">Back to Budgets</a>
    </div>
  </div>
</div>
@endsection

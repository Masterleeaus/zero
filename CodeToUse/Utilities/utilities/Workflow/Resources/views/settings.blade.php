@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">{{ __('workflow.settings_title') }}</h4>
          @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
          @endif
          <form method="POST" action="{{ route('workflow.settings.update') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">{{ __('workflow.pagination') }}</label>
              <input type="number" class="form-control" name="pagination" value="{{ old('pagination', $settings['pagination'] ?? 20) }}">
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('workflow.default_status') }}</label>
              <select class="form-select" name="default_status">
                <option value="active" {{ (old('default_status', $settings['default_status'] ?? 'active')=='active')?'selected':'' }}>Active</option>
                <option value="inactive" {{ (old('default_status', $settings['default_status'] ?? 'active')=='inactive')?'selected':'' }}>Inactive</option>
              </select>
            </div>
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" name="features[api]" value="1" {{ (old('features.api', $settings['features']['api'] ?? true)) ? 'checked' : '' }}>
              <label class="form-check-label">{{ __('workflow.feature_api') }}</label>
            </div>
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" name="features[approvals]" value="1" {{ (old('features.approvals', $settings['features']['approvals'] ?? true)) ? 'checked' : '' }}>
              <label class="form-check-label">{{ __('workflow.feature_approvals') }}</label>
            </div>
            <button class="btn btn-primary" type="submit">{{ __('workflow.save') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">{{ __('New Document Request') }}</h3>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('documents.requests.store') }}">
        @csrf

        <div class="mb-3">
          <label class="form-label">{{ __('Title') }}</label>
          <input name="title" class="form-control" required value="{{ old('title') }}">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">{{ __('Recipient email') }}</label>
            <input name="recipient_email" class="form-control" value="{{ old('recipient_email') }}">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">{{ __('Recipient name') }}</label>
            <input name="recipient_name" class="form-control" value="{{ old('recipient_name') }}">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">{{ __('Due date') }}</label>
          <input type="date" name="due_at" class="form-control" value="{{ old('due_at') }}">
        </div>

        <div class="mb-3">
          <label class="form-label">{{ __('Message') }}</label>
          <textarea name="message" class="form-control" rows="4">{{ old('message') }}</textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="send_email" value="1" id="send_email">
          <label class="form-check-label" for="send_email">{{ __('Send email now') }}</label>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary">{{ __('Create request') }}</button>
          <a class="btn btn-outline-secondary" href="{{ route('documents.requests.index') }}">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

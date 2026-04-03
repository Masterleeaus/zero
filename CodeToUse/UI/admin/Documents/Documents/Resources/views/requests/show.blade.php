@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-1">{{ $req->title }}</h3>
      <div class="text-muted small">{{ __('Status') }}: {{ $req->status }}</div>
    </div>
    <div class="d-flex gap-2">
      @can('documents.requests.send')
        <form method="POST" action="{{ route('documents.requests.resend', $req) }}">
          @csrf
          <button class="btn btn-outline-primary">{{ __('Resend') }}</button>
        </form>
      @endcan
      @can('documents.requests.manage')
        @if($req->status !== 'cancelled')
        <form method="POST" action="{{ route('documents.requests.cancel', $req) }}" onsubmit="return confirm('Cancel request?')">
          @csrf
          <button class="btn btn-outline-danger">{{ __('Cancel') }}</button>
        </form>
        @endif
      @endcan
      <a class="btn btn-outline-secondary" href="{{ route('documents.requests.index') }}">{{ __('Back') }}</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card mb-3">
    <div class="card-body">
      <div><strong>{{ __('Recipient') }}:</strong> {{ $req->recipient_name }} {{ $req->recipient_email }}</div>
      <div><strong>{{ __('Due') }}:</strong> {{ $req->due_at?->format('Y-m-d') }}</div>
      <div class="mt-2 text-muted">{{ $req->message }}</div>

      <hr>
      <div><strong>{{ __('Upload link') }}:</strong></div>
      <div class="small">
        <a href="{{ route('documents.request.public', $req->token) }}" target="_blank">
          {{ route('documents.request.public', $req->token) }}
        </a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h6>{{ __('Uploads') }}</h6>
      @if($req->uploads->isEmpty())
        <div class="text-muted">{{ __('No uploads yet.') }}</div>
      @else
        <ul class="mb-0">
          @foreach($req->uploads as $u)
            <li>{{ $u->original_name }} <span class="text-muted small">({{ $u->created_at }})</span></li>
          @endforeach
        </ul>
      @endif
    </div>
  </div>
</div>
@endsection

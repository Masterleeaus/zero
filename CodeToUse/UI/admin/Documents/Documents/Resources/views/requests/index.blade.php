@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">{{ __('Document Requests') }}</h3>
    <a class="btn btn-primary" href="{{ route('documents.requests.create') }}">{{ __('New request') }}</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>{{ __('Title') }}</th>
            <th>{{ __('Recipient') }}</th>
            <th>{{ __('Due') }}</th>
            <th>{{ __('Status') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        @foreach($requests as $r)
          <tr>
            <td>{{ $r->title }}</td>
            <td class="text-muted">{{ $r->recipient_email }}</td>
            <td class="text-muted">{{ $r->due_at?->format('Y-m-d') }}</td>
            <td><span class="badge bg-secondary">{{ $r->status }}</span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('documents.requests.show', $r) }}">{{ __('View') }}</a>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>

      <div class="mt-3">
        {{ $requests->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ __('Documents — SWMS') }}</h1>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('documents.tags.index') }}">{{ __('Tags') }}</a>
        <a class="btn btn-outline-secondary" href="{{ route('documents.requests.index') }}">{{ __('Requests') }}</a>
      </div>
    <div class="btn-group">
      <a class="btn btn-outline-secondary" href="{{ route('documents.general') }}">{{ __('General') }}</a>
      <a class="btn btn-outline-secondary active" href="{{ route('documents.swms') }}">{{ __('SWMS') }}</a>
      <a class="btn btn-primary" href="{{ route('documents.create') }}">{{ __('New') }}</a>
      <a class="btn btn-secondary" href="{{ route('documents.templates') }}">{{ __('Templates') }}</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ __('Title') }}</th>
          <th>{{ __('Status') }}</th>
          <th>{{ __('Category') }}</th>
          <th>{{ __('Updated') }}</th>
          <th class="text-end">{{ __('Actions') }}</th>
        </tr>
      </thead>
      <tbody>
      @forelse($docs as $doc)
        <tr>
          <td>{{ $doc->id }}</td>
          <td>
            <a href="{{ route('documents.show', $doc) }}">
              {{ $doc->title }}
            </a>
          </td>
          <td>{{ $doc->status ?? 'draft' }}</td>
          <td>{{ $doc->category ?? 'SWMS' }}</td>
          <td>{{ optional($doc->updated_at)->format('d M Y H:i') }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-dark" href="{{ route('documents.show', $doc) }}">{{ __('View') }}</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('documents.edit', $doc) }}">{{ __('Edit') }}</a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-muted text-center">
            {{ __('No SWMS documents yet. Use the "New" button to create one.') }}
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $docs->links() }}
  </div>
</div>
@endsection

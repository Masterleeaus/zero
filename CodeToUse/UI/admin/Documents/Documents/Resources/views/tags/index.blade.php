@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">{{ __('Document Tags') }}</h3>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @can('documents.tags.manage')
  <div class="card mb-3">
    <div class="card-body">
      <form method="POST" action="{{ route('documents.tags.store') }}" class="row g-2">
        @csrf
        <div class="col-md-4">
          <input class="form-control" name="name" placeholder="Tag name" required>
        </div>
        <div class="col-md-3">
          <input class="form-control" name="bg_color" placeholder="BG color (e.g. #0ea5e9)">
        </div>
        <div class="col-md-3">
          <input class="form-control" name="text_color" placeholder="Text color (e.g. #ffffff)">
        </div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-primary">{{ __('Save') }}</button>
        </div>
      </form>
    </div>
  </div>
  @endcan

  <div class="card">
    <div class="card-body">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>{{ __('Tag') }}</th>
            <th>{{ __('Slug') }}</th>
            <th>{{ __('Colors') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        @foreach($tags as $tag)
          <tr>
            <td>
              <span class="badge" style="background: {{ $tag->bg_color ?? '#e5e7eb' }}; color: {{ $tag->text_color ?? '#111827' }};">
                {{ $tag->name }}
              </span>
            </td>
            <td>{{ $tag->slug }}</td>
            <td class="text-muted small">{{ $tag->bg_color }} / {{ $tag->text_color }}</td>
            <td class="text-end">
              @can('documents.tags.manage')
                <form method="POST" action="{{ route('documents.tags.destroy', $tag) }}" onsubmit="return confirm('Delete tag?')" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                </form>
              @endcan
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>

      <div class="mt-3">
        {{ $tags->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

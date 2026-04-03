@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ __('Document Templates') }}</h1>
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
      {{ __('Create Document from Template') }}
    </a>
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ __('Name') }}</th>
          <th>{{ __('Category') }}</th>
          <th>{{ __('Subcategory') }}</th>
          <th>{{ __('Actions') }}</th>
        </tr>
      </thead>
      <tbody>
      @forelse($templates as $tpl)
        <tr>
          <td>{{ $tpl->id }}</td>
          <td>{{ $tpl->name }}</td>
          <td>{{ $tpl->category ?? '—' }}</td>
          <td>{{ $tpl->subcategory ?? '—' }}</td>
          <td class="text-nowrap">
            <a href="{{ route('documents.create', ['template' => $tpl->slug]) }}"
               class="btn btn-sm btn-outline-primary">
              {{ __('Use') }}
            </a>
            <a href="{{ route('documents.templates.print', $tpl->slug) }}"
               target="_blank"
               class="btn btn-sm btn-outline-secondary">
              {{ __('Print') }}
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="text-center text-muted">
            {{ __('No templates defined yet.') }}
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

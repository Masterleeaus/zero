@extends('layouts.app')

@push('styles')
<style>
  .documents-template-tabs .nav-link { margin-right: .5rem; }
  .documents-template-tabs .badge { font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="mb-0">{{ __('Document Templates') }}</h1>
      <div class="text-muted small">{{ __('Choose a template to prefill a new document.') }}</div>
    </div>

    <a href="{{ route('documents.create') }}" class="btn btn-primary">
      <i class="fa fa-plus"></i> {{ __('New Document') }}
    </a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

        {{-- Tabs: Docs vs SWMS --}}
        <ul class="nav nav-pills documents-template-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($tab ?? 'docs') === 'docs' ? 'active' : '' }}"
               href="{{ route('documents.templates', ['tab' => 'docs', 'q' => $q ?? null, 'trade' => $trade ?? null]) }}">
              {{ __('Docs') }}
              <span class="badge bg-light text-dark ms-1">{{ (int)($counts['docs'] ?? 0) }}</span>
            </a>
          </li>

          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($tab ?? 'docs') === 'swms' ? 'active' : '' }}"
               href="{{ route('documents.templates', ['tab' => 'swms', 'q' => $q ?? null, 'trade' => $trade ?? null]) }}">
              {{ __('SWMS') }}
              <span class="badge bg-light text-dark ms-1">{{ (int)($counts['swms'] ?? 0) }}</span>
            </a>
          </li>
        </ul>

        {{-- Search --}}
        <form method="GET" action="{{ route('documents.templates') }}" class="d-flex align-items-center">
          <input type="hidden" name="tab" value="{{ $tab ?? 'docs' }}">

          <select name="trade" class="form-select me-2" style="width:auto;">
            <option value="">{{ __('All trades') }}</option>
            @foreach(($tradeOptions ?? []) as $t)
              <option value="{{ $t }}" {{ ($trade ?? '') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
          </select>
          <div class="input-group">
            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="{{ __('Search templates…') }}">
            <button class="btn btn-outline-secondary" type="submit">
              <i class="fa fa-search"></i>
            </button>
          </div>
        </form>

      </div>

      @if (($counts['docs'] ?? 0) + ($counts['swms'] ?? 0) === 0)
        <div class="alert alert-warning mt-3 mb-0">
          <strong>{{ __('No templates installed yet.') }}</strong>
          <div class="small mt-1">
            {{ __('Run the Documents module seeder to install default templates:') }}
            <div class="mt-2">
              <code>php artisan module:seed Documents</code>
              <span class="text-muted">/</span>
              <code>php artisan db:seed --class="Modules\Documents\Database\Seeders\DocumentsTemplateSeeder"</code>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 42px;">#</th>
              <th>{{ __('Name') }}</th>
              <th style="width: 140px;">{{ __('Category') }}</th>
              <th>{{ __('Description') }}</th>
              <th style="width: 220px;" class="text-end">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($templates as $i => $t)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td class="fw-semibold">{{ $t->name }}</td>
                <td>
                  @php $cat = $t->category ?: 'Docs'; @endphp
                  <span class="badge {{ $cat === 'SWMS' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                    {{ $cat }}
                  </span>
                </td>
                <td class="text-muted">{{ $t->description }}</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary"
                     href="{{ route('documents.create', ['template' => $t->slug]) }}">
                    <i class="fa fa-bolt"></i> {{ __('Use') }}
                  </a>

                  <a class="btn btn-sm btn-outline-secondary"
                     href="{{ route('documents.templates.print', $t->slug) }}" target="_blank" rel="noopener">
                    <i class="fa fa-print"></i> {{ __('Print') }}
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  {{ __('No templates found for this section.') }}
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection

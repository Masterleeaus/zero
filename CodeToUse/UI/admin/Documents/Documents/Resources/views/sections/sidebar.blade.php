@php
    // Sidebar-safe permission gate:
    // - Worksuite often provides user()->permission(...)
    // - Fallback to Laravel Gates if available
    $canManageDocuments = true;

    try {
        if (function_exists('user') && user()) {
            if (method_exists(user(), 'permission')) {
                $perm = user()->permission('manage_documents');
                // Worksuite commonly returns 'all' / 'added' / 'owned' / 'both' / 'none'
                $canManageDocuments = ($perm !== 'none' && $perm !== false && $perm !== null);
            } elseif (method_exists(user(), 'can')) {
                $canManageDocuments = user()->can('manage_documents');
            }
        } elseif (function_exists('auth') && auth()->check() && auth()->user() && method_exists(auth()->user(), 'can')) {
            $canManageDocuments = auth()->user()->can('manage_documents');
        }
    } catch (\Throwable $e) {
        // Never break global sidebar render
        $canManageDocuments = true;
    }

    $hasAnyDocumentsRoute = \Illuminate\Support\Facades\Route::has('documents.manager.dashboard')
        || \Illuminate\Support\Facades\Route::has('documents.general')
        || \Illuminate\Support\Facades\Route::has('documents.swms')
        || \Illuminate\Support\Facades\Route::has('documents.create')
        || \Illuminate\Support\Facades\Route::has('documents.templates')
        || \Illuminate\Support\Facades\Route::has('documents.files.index');
@endphp

@if($canManageDocuments && $hasAnyDocumentsRoute)
<li class="sidebar-item">
  <a class="sidebar-link sidebar-toggle"
     href="{{ \Illuminate\Support\Facades\Route::has('documents.manager.dashboard') ? route('documents.manager.dashboard') : '#' }}">
    <i class="fa fa-file-alt sidebar-icon"></i>
    <span>{{ __('Documents') }}</span>
  </a>

  <ul class="sidebar-submenu">
    @if(Route::has('documents.manager.dashboard'))
      <li>
        <a href="{{ route('documents.manager.dashboard') }}">
          <i class="fa fa-tachometer-alt"></i>
          {{ __('Dashboard') }}
        </a>
      </li>
    @endif

    @if(Route::has('documents.general'))
      <li>
        <a href="{{ route('documents.general') }}">
          <i class="fa fa-file-lines"></i>
          {{ __('All Documents') }}
        </a>
      </li>
    @endif

    @if(Route::has('documents.swms'))
      <li>
        <a href="{{ route('documents.swms') }}">
          <i class="fa fa-hard-hat"></i>
          {{ __('All SWMS') }}
        </a>
      </li>
    @endif

    @if(Route::has('documents.create'))
      <li>
        <a href="{{ route('documents.create') }}">
          <i class="fa fa-plus-circle"></i>
          {{ __('Create Document') }}
        </a>
      </li>
    @endif

    @if(Route::has('documents.templates'))
      <li>
        <a href="{{ route('documents.templates') }}">
          <i class="fa fa-layer-group"></i>
          {{ __('Document Library') }}
        </a>
      </li>
    @endif

    @if(Route::has('documents.files.index'))
      <li>
        <a href="{{ route('documents.files.index') }}">
          <i class="fa fa-folder-open"></i>
          {{ __('Files & Folders') }}
        </a>
      </li>
    @endif
  </ul>
</li>
@endif


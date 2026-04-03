@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ __('Folders') }}</h1>
    <a href="{{ route('documents.folders.create', ['parent_id' => optional($currentFolder)->id]) }}"
       class="btn btn-primary">
      <i class="fa fa-plus-circle"></i> {{ __('New Folder') }}
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @if($breadcrumbs)
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('documents.folders.index') }}">{{ __('Root') }}</a>
        </li>
        @foreach($breadcrumbs as $crumb)
          <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
            @if($loop->last)
              {{ $crumb->name }}
            @else
              <a href="{{ route('documents.folders.index', ['folder_id' => $crumb->id]) }}">
                {{ $crumb->name }}
              </a>
            @endif
          </li>
        @endforeach
      </ol>
    </nav>
  @endif

  <div class="row">
    <div class="col-md-4">
      <h5>{{ __('Folder Tree') }}</h5>
      <ul class="list-group">
        @foreach($rootFolders as $folder)
          <li class="list-group-item">
            <a href="{{ route('documents.folders.index', ['folder_id' => $folder->id]) }}">
              <i class="fa fa-folder"></i> {{ $folder->name }}
            </a>
          </li>
        @endforeach
      </ul>
    </div>
    <div class="col-md-8">
      <h5>{{ $currentFolder ? $currentFolder->name : __('Root') }}</h5>

      @if($currentFolder)
        <p>{{ $currentFolder->description }}</p>
        <form action="{{ route('documents.folders.destroy', $currentFolder) }}"
              method="POST"
              onsubmit="return confirm('{{ __('Delete this folder?') }}')">
          @csrf
          @method('DELETE')
          <button class="btn btn-sm btn-outline-danger">
            <i class="fa fa-trash"></i> {{ __('Delete Folder') }}
          </button>
          <a href="{{ route('documents.folders.edit', $currentFolder) }}"
             class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-edit"></i> {{ __('Rename') }}
          </a>
        </form>
        <hr>
      @endif

      <h6 class="mt-3">{{ __('Subfolders') }}</h6>
      <ul class="list-group mb-3">
        @forelse(optional($currentFolder)->children ?? $rootFolders as $sub)
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <a href="{{ route('documents.folders.index', ['folder_id' => $sub->id]) }}">
              <i class="fa fa-folder"></i> {{ $sub->name }}
            </a>
            <small class="text-muted">{{ $sub->created_at->format('d M Y') }}</small>
          </li>
        @empty
          <li class="list-group-item text-muted">{{ __('No subfolders.') }}</li>
        @endforelse
      </ul>

      <a href="{{ route('documents.files.index', ['folder_id' => optional($currentFolder)->id]) }}"
         class="btn btn-outline-primary">
        <i class="fa fa-file"></i> {{ __('View Files in this Folder') }}
      </a>
    </div>
  </div>
</div>
@endsection

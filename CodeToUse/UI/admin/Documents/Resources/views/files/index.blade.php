@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ __('Files') }}</h1>
    <a href="{{ route('documents.files.create', ['folder_id' => optional($currentFolder)->id]) }}"
       class="btn btn-primary">
      <i class="fa fa-upload"></i> {{ __('Upload File') }}
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($breadcrumbs)
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('documents.files.index') }}">{{ __('Root') }}</a>
        </li>
        @foreach($breadcrumbs as $crumb)
          <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
            @if($loop->last)
              {{ $crumb->name }}
            @else
              <a href="{{ route('documents.files.index', ['folder_id' => $crumb->id]) }}">
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
      <h5>{{ __('Folders') }}</h5>
      <ul class="list-group mb-3">
        @foreach($rootFolders as $folder)
          <li class="list-group-item {{ optional($currentFolder)->id === $folder->id ? 'active' : '' }}">
            <a class="{{ optional($currentFolder)->id === $folder->id ? 'text-white' : '' }}"
               href="{{ route('documents.files.index', ['folder_id' => $folder->id]) }}">
              <i class="fa fa-folder"></i> {{ $folder->name }}
            </a>
          </li>
        @endforeach
      </ul>

      <a href="{{ route('documents.folders.index', ['folder_id' => optional($currentFolder)->id]) }}"
         class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-sitemap"></i> {{ __('Manage Folders') }}
      </a>
    </div>

    <div class="col-md-8">
      <h5>{{ $currentFolder ? $currentFolder->name : __('Root') }}</h5>

      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ __('File Name') }}</th>
            <th>{{ __('Size') }}</th>
            <th>{{ __('Uploaded') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        @forelse($files as $file)
          <tr>
            <td>{{ $file->name }}</td>
            <td>{{ number_format($file->size / 1024, 1) }} KB</td>
            <td>{{ $file->created_at->format('d M Y') }}</td>
            <td class="text-end">
              <a href="{{ route('documents.attachments.download', $file->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-download"></i>
              </a>
              <a href="{{ route('documents.files.edit', $file) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-edit"></i>
              </a>
              <form action="{{ route('documents.files.destroy', $file) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('{{ __('Delete this file?') }}')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                  <i class="fa fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-muted">{{ __('No files in this folder.') }}</td>
          </tr>
        @endforelse
        </tbody>
      </table>

      {{ $files->links() }}
    </div>
  </div>
</div>
@endsection

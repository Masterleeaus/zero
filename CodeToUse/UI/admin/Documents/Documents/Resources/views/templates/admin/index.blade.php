@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Template Governance</h3>

  <form method="GET" class="mb-3">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search templates..." class="form-control" />
  </form>

  <table class="table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Category</th>
        <th>Trade</th>
        <th>Role</th>
        <th>Published</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($templates as $t)
      <tr>
        <td>{{ $t->title }}</td>
        <td>{{ $t->category }}</td>
        <td>{{ $t->trade }}</td>
        <td>{{ $t->role_key }}</td>
        <td>
          @if($t->published_at)
            <span class="badge bg-success">Yes</span>
          @else
            <span class="badge bg-secondary">No</span>
          @endif
        </td>
        <td class="text-end">
          <a href="{{ route('documents.templates.admin.edit', $t->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
          @if($t->published_at)
            <form method="POST" action="{{ route('documents.templates.admin.unpublish', $t->id) }}" class="d-inline">
              @csrf
              <button class="btn btn-sm btn-outline-warning">Unpublish</button>
            </form>
          @else
            <form method="POST" action="{{ route('documents.templates.admin.publish', $t->id) }}" class="d-inline">
              @csrf
              <button class="btn btn-sm btn-outline-success">Publish</button>
            </form>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{ $templates->links() }}
</div>
@endsection

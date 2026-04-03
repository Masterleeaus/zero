@extends('titantalk::layouts.base')
@section('title','Entities')
@section('body')
<a href="{{ route('titantalk.entities.create') }}" class="btn btn-primary mb-3">New Entity</a>
<a href="{{ route('titantalk.entities.export') }}" class="btn btn-secondary mb-3">Export</a>
<form method="POST" action="{{ route('titantalk.entities.import') }}" enctype="multipart/form-data" class="d-inline">
  @csrf <input type="file" name="file" required> <button class="btn btn-secondary">Import</button>
</form>
<table class="table table-sm"><thead><tr><th>ID</th><th>Name</th><th>Values</th><th></th></tr></thead><tbody>
@foreach($entities as $e)
<tr><td>{{ $e->id }}</td><td>{{ $e->name }}</td><td>{{ is_array($e->values)? implode(', ',$e->values):'' }}</td>
<td>
  <a class="btn btn-sm btn-outline-primary" href="{{ route('titantalk.entities.edit',$e->id) }}">Edit</a>
  <form action="{{ route('titantalk.entities.delete',$e->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger">Delete</button>
  </form>
</td></tr>
@endforeach
</tbody></table>
{{ $entities->links() }}
@endsection

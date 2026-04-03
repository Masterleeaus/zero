@extends('titantalk::layouts.base')
@section('title','Intents')
@section('body')
<a href="{{ route('titantalk.intents.create') }}" class="btn btn-primary mb-3">New Intent</a>
<a href="{{ route('titantalk.intents.export') }}" class="btn btn-secondary mb-3">Export</a>
<form method="POST" action="{{ route('titantalk.intents.import') }}" enctype="multipart/form-data" class="d-inline">
  @csrf <input type="file" name="file" required> <button class="btn btn-secondary">Import</button>
</form>
<table class="table table-sm"><thead><tr><th>ID</th><th>Name</th><th>Description</th><th></th></tr></thead><tbody>
@foreach($intents as $i)
<tr><td>{{ $i->id }}</td><td>{{ $i->name }}</td><td>{{ $i->description }}</td>
<td>
  <a class="btn btn-sm btn-outline-primary" href="{{ route('titantalk.intents.edit',$i->id) }}">Edit</a>
  <form action="{{ route('titantalk.intents.delete',$i->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger">Delete</button>
  </form>
</td></tr>
@endforeach
</tbody></table>
{{ $intents->links() }}
@endsection

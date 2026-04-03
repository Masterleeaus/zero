@extends('titantalk::layouts.base')
@section('title','Channels')
@section('body')
<a href="{{ route('titantalk.channels.create') }}" class="btn btn-primary mb-3">New Channel</a>
<table class="table table-sm"><thead><tr><th>ID</th><th>Name</th><th>Driver</th><th>Enabled</th><th></th></tr></thead><tbody>
@foreach($channels as $c)
<tr><td>{{ $c->id }}</td><td>{{ $c->name }}</td><td>{{ $c->driver }}</td><td>{{ $c->enabled ? 'Yes' : 'No' }}</td>
<td>
  <a class="btn btn-sm btn-outline-primary" href="{{ route('titantalk.channels.edit',$c->id) }}">Edit</a>
  <form action="{{ route('titantalk.channels.delete',$c->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger">Delete</button>
  </form>
</td></tr>
@endforeach
</tbody></table>
{{ $channels->links() }}
@endsection

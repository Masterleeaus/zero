@extends('titanhello::layouts.master')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Ring Groups</h4>
  <a href="{{ route('titanhello.routing.ringgroups.create') }}" class="btn btn-primary">Create Ring Group</a>
</div>

@include('titanhello::partials.flash')

<div class="card">
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead><tr><th>Name</th><th>Strategy</th><th>Timeout</th><th>Enabled</th><th></th></tr></thead>
      <tbody>
      @foreach($groups as $g)
        <tr>
          <td>{{ $g->name }}</td>
          <td>{{ $g->strategy }}</td>
          <td>{{ $g->timeout_seconds }}s</td>
          <td>{!! $g->enabled ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('titanhello.routing.ringgroups.edit', $g->id) }}">Edit</a>
            <form action="{{ route('titanhello.routing.ringgroups.delete', $g->id) }}" method="POST" style="display:inline">
              @csrf
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete ring group?')">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $groups->links() }}</div>

@endsection

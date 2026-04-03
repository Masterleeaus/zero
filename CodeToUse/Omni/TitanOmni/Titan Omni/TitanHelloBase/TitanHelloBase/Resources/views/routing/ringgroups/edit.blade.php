@extends('titanhello::layouts.master')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Edit Ring Group</h4>
  <a href="{{ route('titanhello.routing.ringgroups.index') }}" class="btn btn-light">Back</a>
</div>

@include('titanhello::partials.flash')

<form method="POST" action="{{ route('titanhello.routing.ringgroups.update', $group->id) }}" class="card card-body mb-3">
  @csrf
  @include('titanhello::routing/ringgroups/form', ['group' => $group])
  <div class="mt-3">
    <button class="btn btn-primary">Update</button>
  </div>
</form>

<div class="card">
  <div class="card-header"><strong>Members</strong></div>
  <div class="card-body">
    <form method="POST" action="{{ route('titanhello.routing.ringgroups.members.add', $group->id) }}" class="row g-2">
      @csrf
      <div class="col-md-3"><input class="form-control" name="label" placeholder="Label"></div>
      <div class="col-md-3"><input class="form-control" name="phone_number" placeholder="+614..." required></div>
      <div class="col-md-2"><input class="form-control" name="priority" type="number" value="10" min="1" max="999" required></div>
      <div class="col-md-2">
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="enabled" value="1" checked>
          <label class="form-check-label">Enabled</label>
        </div>
      </div>
      <div class="col-md-2"><button class="btn btn-outline-primary w-100">Add</button></div>
    </form>

    <hr>

    <table class="table">
      <thead><tr><th>Label</th><th>Phone</th><th>Priority</th><th>Enabled</th><th></th></tr></thead>
      <tbody>
      @foreach($members as $m)
        <tr>
          <td>{{ $m->label }}</td>
          <td>{{ $m->phone_number }}</td>
          <td>{{ $m->priority }}</td>
          <td>{!! $m->enabled ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
          <td class="text-end">
            <form method="POST" action="{{ route('titanhello.routing.ringgroups.members.delete', [$group->id, $m->id]) }}">
              @csrf
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove member?')">Remove</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection

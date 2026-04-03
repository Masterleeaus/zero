@extends('titanhello::layouts.master')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Edit IVR Menu</h4>
  <a href="{{ route('titanhello.routing.ivr.index') }}" class="btn btn-light">Back</a>
</div>

@include('titanhello::partials.flash')

<form method="POST" action="{{ route('titanhello.routing.ivr.update', $menu->id) }}" class="card card-body mb-3">
  @csrf
  @include('titanhello::routing/ivr/form', ['menu' => $menu])
  <div class="mt-3">
    <button class="btn btn-primary">Update</button>
  </div>
</form>

<div class="card">
  <div class="card-header"><strong>Options</strong></div>
  <div class="card-body">
    <form method="POST" action="{{ route('titanhello.routing.ivr.options.add', $menu->id) }}" class="row g-2">
      @csrf
      <div class="col-md-2"><input class="form-control" name="dtmf" placeholder="1" required></div>
      <div class="col-md-4"><input class="form-control" name="label" placeholder="Label"></div>
      <div class="col-md-3">
        <select name="action_type" class="form-select">
          <option value="ring_group">Ring Group</option>
          <option value="ivr">IVR Menu</option>
          <option value="voicemail">Voicemail</option>
          <option value="hangup">Hangup</option>
        </select>
      </div>
      <div class="col-md-2"><input class="form-control" name="action_target_id" placeholder="Target ID"></div>
      <div class="col-md-1"><button class="btn btn-outline-primary w-100">Add</button></div>
    </form>

    <hr>

    <table class="table">
      <thead><tr><th>DTMF</th><th>Label</th><th>Action</th><th>Target</th><th></th></tr></thead>
      <tbody>
      @foreach($options as $o)
        <tr>
          <td>{{ $o->dtmf }}</td>
          <td>{{ $o->label }}</td>
          <td>{{ $o->action_type }}</td>
          <td>{{ $o->action_target_id }}</td>
          <td class="text-end">
            <form method="POST" action="{{ route('titanhello.routing.ivr.options.delete', [$menu->id, $o->id]) }}">
              @csrf
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove option?')">Remove</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection

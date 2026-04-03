@extends('titanhello::layouts.master')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>IVR Menus</h4>
  <a href="{{ route('titanhello.routing.ivr.create') }}" class="btn btn-primary">Create IVR Menu</a>
</div>

@include('titanhello::partials.flash')

<div class="card">
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead><tr><th>Name</th><th>Enabled</th><th></th></tr></thead>
      <tbody>
      @foreach($menus as $m)
        <tr>
          <td>{{ $m->name }}</td>
          <td>{!! $m->enabled ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('titanhello.routing.ivr.edit', $m->id) }}">Edit</a>
            <form method="POST" action="{{ route('titanhello.routing.ivr.delete', $m->id) }}" style="display:inline">
              @csrf
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete IVR menu?')">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3">{{ $menus->links() }}</div>

@endsection

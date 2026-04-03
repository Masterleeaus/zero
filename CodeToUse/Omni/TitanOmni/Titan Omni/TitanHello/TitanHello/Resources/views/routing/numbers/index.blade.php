@extends('titanhello::layouts.master')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Inbound Numbers</h4>
  <a href="{{ route('titanhello.routing.numbers.create') }}" class="btn btn-primary">Add Number</a>
</div>

@include('titanhello::partials.flash')

<div class="card">
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Number</th><th>Label</th><th>Mode</th><th>Target</th><th>Enabled</th><th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($numbers as $n)
        <tr>
          <td>{{ $n->phone_number }}</td>
          <td>{{ $n->label }}</td>
          <td>{{ $n->mode }}</td>
          <td>{{ $n->target_id }}</td>
          <td>{!! $n->enabled ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('titanhello.routing.numbers.edit', $n->id) }}">Edit</a>
            <form action="{{ route('titanhello.routing.numbers.delete', $n->id) }}" method="POST" style="display:inline">
              @csrf
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this number?')">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  {{ $numbers->links() }}
</div>

@endsection

@extends('layouts.app')
@section('content')
<div class="container py-4">
  @include('contracts::partials.brand')
  <div class="d-flex justify-content-between mb-3">
    <h3>Contracts</h3>
    <a href="{{ route('contracts.create') }}" class="btn btn-primary">New Contract</a>
  </div>
  <table class="table table-striped">
    <thead><tr><th>#</th><th>Title</th><th>Status</th><th>Client</th><th>Updated</th><th></th></tr></thead>
    <tbody>
    @foreach($contracts as $c)
      <tr>
        <td>{{ $c->number }}</td>
        <td>{{ $c->title }}</td>
        <td>{{ $c->status }}</td>
        <td>{{ $c->client_id ?? '-' }}</td>
        <td>{{ $c->updated_at->diffForHumans() }}</td>
        <td class="text-end"><a href="{{ route('contracts.show', $c->id) }}" class="btn btn-sm btn-outline-secondary">Open</a></td>
      </tr>
    @endforeach
    </tbody>
  </table>
  {{ $contracts->links() }}
</div>
@endsection

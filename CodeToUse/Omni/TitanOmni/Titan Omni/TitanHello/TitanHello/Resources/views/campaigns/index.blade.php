@extends('titanhello::layouts.master')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Dial Campaigns</h4>
  <a href="{{ route('titanhello.campaigns.create') }}" class="btn btn-primary">Create Campaign</a>
</div>

@include('titanhello::partials.flash')

<div class="card">
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead><tr><th>Name</th><th>Status</th><th>Enabled</th><th></th></tr></thead>
      <tbody>
      @foreach($campaigns as $c)
        <tr>
          <td>{{ $c->name }}</td>
          <td>{{ $c->status }}</td>
          <td>{!! $c->enabled ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('titanhello.campaigns.edit', $c->id) }}">Manage</a>
            <form method="POST" action="{{ route('titanhello.campaigns.delete', $c->id) }}" style="display:inline">
              @csrf
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete campaign?')">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3">{{ $campaigns->links() }}</div>

@endsection

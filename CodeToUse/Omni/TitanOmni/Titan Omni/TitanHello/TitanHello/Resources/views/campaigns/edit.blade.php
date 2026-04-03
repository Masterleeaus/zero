@extends('titanhello::layouts.master')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Manage Campaign</h4>
  <a href="{{ route('titanhello.campaigns.index') }}" class="btn btn-light">Back</a>
</div>

@include('titanhello::partials.flash')

<form method="POST" action="{{ route('titanhello.campaigns.update', $campaign->id) }}" class="card card-body mb-3">
  @csrf
  @include('titanhello::campaigns/form', ['campaign' => $campaign])
  <div class="mt-3">
    <button class="btn btn-primary">Update</button>
  </div>
</form>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between">
    <strong>Contacts</strong>
    <form method="POST" action="{{ route('titanhello.campaigns.run', $campaign->id) }}">
      @csrf
      <button class="btn btn-sm btn-outline-primary">Run Batch (10)</button>
    </form>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('titanhello.campaigns.contacts.add', $campaign->id) }}" class="row g-2 mb-3">
      @csrf
      <div class="col-md-4"><input class="form-control" name="name" placeholder="Name"></div>
      <div class="col-md-4"><input class="form-control" name="phone_number" placeholder="+614..." required></div>
      <div class="col-md-4"><button class="btn btn-outline-primary w-100">Add Contact</button></div>
    </form>

    <table class="table">
      <thead><tr><th>Name</th><th>Phone</th><th>Status</th><th>Attempts</th><th>Last</th></tr></thead>
      <tbody>
      @foreach($contacts as $ct)
        <tr>
          <td>{{ $ct->name }}</td>
          <td>{{ $ct->phone_number }}</td>
          <td>{{ $ct->status }}</td>
          <td>{{ $ct->attempt_count }}</td>
          <td>{{ $ct->last_attempt_at }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
    <div class="mt-2">{{ $contacts->links() }}</div>
  </div>
</div>

@endsection

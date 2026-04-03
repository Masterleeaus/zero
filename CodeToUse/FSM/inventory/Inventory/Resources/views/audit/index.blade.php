@extends('inventory::layout')
@section('content')
<h2>Audit Log</h2>
<form method="GET" class="controls">
  <input name="action" value="{{ request('action') }}" placeholder="Action contains">
  <input type="number" name="user_id" value="{{ request('user_id') }}" placeholder="User ID">
  <span class="badge">Date</span>
  <input type="date" name="date_from" value="{{ request('date_from') }}">
  <input type="date" name="date_to" value="{{ request('date_to') }}">
  <button class="btn btn-primary">Filter</button>
  <a class="btn" href="{{ route('inventory.audit.index') }}">Reset</a>
</form>
<table class="table" data-enhanced>
  <thead><tr><th>ID</th><th>Action</th><th>User</th><th>Tenant</th><th>At</th><th></th></tr></thead>
  <tbody>
  @foreach($rows as $r)
    <tr>
      <td>{{ $r->id }}</td>
      <td>{{ $r->action }}</td>
      <td>{{ $r->user_id }}</td>
      <td>{{ $r->tenant_id }}</td>
      <td>{{ $r->created_at }}</td>
      <td><a class="btn" href="{{ route('inventory.audit.show',$r->id) }}">View</a></td>
    </tr>
  @endforeach
  </tbody>
</table>
{{ $rows->links() }}
@endsection

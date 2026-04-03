@extends('inventory::layout')
@section('content')
<h2>Audit #{{ $row->id }}</h2>
<p><span class="badge">Action</span> {{ $row->action }}</p>
<p><span class="badge">User</span> {{ $row->user_id }}</p>
<p><span class="badge">Tenant</span> {{ $row->tenant_id }}</p>
<p><span class="badge">When</span> {{ $row->created_at }}</p>
<h3>Context</h3>
<pre style="background:#0f172a; padding:10px; border:1px solid #1f2937; border-radius:8px; white-space:pre-wrap; word-break:break-word;">{{ json_encode($row->context, JSON_PRETTY_PRINT) }}</pre>
@endsection

@extends('layouts.app')
@section('content')
<div class="container">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3">AIConverse</h1>
    <a href="{{ route('titantalk.ping') }}" class="btn btn-outline-secondary">Ping</a>
  </div>
  <div class="card">
    <div class="card-body">
      <p class="mb-2">Module is installed and your tenant is entitled. Replace this with your chat UI.</p>
      <ul class="mb-0">
        <li>Route: <code>/aiconverse</code></li>
        <li>View namespace: <code>titantalk::</code></li>
        <li>Feature key: <code>aiconverse</code></li>
      </ul>
    </div>
  </div>
</div>
@endsection

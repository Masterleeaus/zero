@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Workflow Module — Wiring Check</h4>
          <p class="card-text">If you can see this, the module is installed, routes loaded, and your role has <code>view_workflow</code>.</p>
          <ul>
            <li>Route name: <code>workflow.check</code></li>
            <li>Permission required: <code>view_workflow</code></li>
            <li>Plan alias: <code>workflow</code></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

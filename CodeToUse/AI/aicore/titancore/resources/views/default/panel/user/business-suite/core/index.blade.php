@extends('panel.layout.app')

@section('title', 'Titan Core')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="mb-2">Titan Core Status</h3>
                    <p class="text-muted mb-0">Functional core bootstrap state across Zero, Pulse, Omni, Agents, and the Nexus multi-core pipeline.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5>Modules</h5>
                    <pre class="small mb-0">{{ json_encode($coreStatus['manifest']['modules'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5>Runtimes</h5>
                    <pre class="small mb-0">{{ json_encode($coreStatus['manifest']['runtimes'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5>Nexus Pipeline</h5>
                    <pre class="small mb-0">{{ json_encode($coreStatus['nexus'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5>Tools</h5>
                    <pre class="small mb-0">{{ json_encode($coreStatus['manifest']['tools'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

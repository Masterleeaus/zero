@extends(preference('layout.master'))

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <h2 class="page-title">
                            Titan Core
                        </h2>
                        <div class="text-muted mt-1">Runtime status and health</div>
                    </div>
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Core Status</h3>
                        </div>
                        <div class="card-body">
                            <pre class="bg-dark text-white p-3 rounded" style="font-size:0.8rem;">{{ json_encode($coreStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">AI Router Status</h3>
                        </div>
                        <div class="card-body">
                            <pre class="bg-dark text-white p-3 rounded" style="font-size:0.8rem;">{{ json_encode($routerStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

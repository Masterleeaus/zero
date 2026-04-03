@extends('layouts.app')
@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    @foreach([
      ['My Documents',$myDocuments],
      ['My SWMS',$mySwms],
      ['Company Documents',$companyDocuments],
      ['Company SWMS',$companySwms]
    ] as [$label,$val])
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6>{{ $label }}</h6>
            <h3>{{ $val }}</h3>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">My Documents vs SWMS</div>
        <div class="card-body"><canvas id="myChart"></canvas></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Company Totals</div>
        <div class="card-body"><canvas id="companyChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Recent Templates</div>
        <div class="card-body">
          <ul class="list-group">
            @foreach($recentTemplates as $t)
              <li class="list-group-item d-flex justify-content-between">
                <span>{{ $t->name }}</span>
                <small class="text-muted">{{ \Carbon\Carbon::parse($t->updated_at)->diffForHumans() }}</small>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      @includeIf('documents::widgets.documents-assistant')
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  new Chart(document.getElementById('myChart'), {
    type:'bar',
    data:{
      labels:['Documents','SWMS'],
      datasets:[{data:[{{ $myDocuments }},{{ $mySwms }}],backgroundColor:['#6c757d','#adb5bd']}]
    },options:{plugins:{legend:{display:false}}}
  });
  new Chart(document.getElementById('companyChart'), {
    type:'doughnut',
    data:{
      labels:['Documents','SWMS'],
      datasets:[{data:[{{ $companyDocuments }},{{ $companySwms }}],backgroundColor:['#ced4da','#dee2e6']}]
    },options:{plugins:{legend:{position:'bottom'}}}
  });
</script>
@endpush

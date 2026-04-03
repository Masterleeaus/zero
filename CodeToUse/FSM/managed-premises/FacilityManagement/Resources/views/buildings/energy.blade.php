@extends('facility::layouts.master')
@section('content')
<div class="container py-3">
  <h2>Energy Breakdown — {{ $building->name }}</h2>
  <p class="text-muted">Last 12 months; per meter type.</p>
  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead>
        <tr>
          <th>Month</th>
          <th>Power</th>
          <th>Water</th>
          <th>Gas</th>
        </tr>
      </thead>
      <tbody>
        @foreach($months as $m)
          <tr>
            <td>{{ $m }}</td>
            <td>{{ optional(collect($series['power'])->firstWhere('ym',$m))['total'] ?? 0 }}</td>
            <td>{{ optional(collect($series['water'])->firstWhere('ym',$m))['total'] ?? 0 }}</td>
            <td>{{ optional(collect($series['gas'])->firstWhere('ym',$m))['total'] ?? 0 }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

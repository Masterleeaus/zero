@extends('facility::layouts.master')
@section('content')
<div class="container py-3">
  <h2>Facilities Dashboard</h2>
  <div class="row g-3 mb-3">
    @php
      $cards = [
        ['Sites', $metrics['sites'] ?? 0],
        ['Buildings', $metrics['buildings'] ?? 0],
        ['Units', $metrics['units'] ?? 0],
        ['Assets', $metrics['assets'] ?? 0],
        ['Inspections (next 7d)', $metrics['ins_due'] ?? 0],
        ['Docs expiring (30d)', $metrics['docs_exp'] ?? 0],
        ['Occupied', $metrics['occupied'] ?? 0],
        ['Vacant', $metrics['vacant'] ?? 0],
      ];
    @endphp
    @foreach($cards as $c)
    <div class="col-6 col-md-3">
      <div class="card text-center p-3">
        <div class="fs-6 text-muted">{{ $c[0] }}</div>
        <div class="fs-3 fw-bold">{{ $c[1] }}</div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="card p-3">
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Energy Trend (last 12 months)</h5>
      <div>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('facility.energy.csv', ['months'=>12, 'meter_type'=>'power']) }}">Download CSV</a>
      </div>
    </div>
    <div class="mt-3">
      <img src="{{ route('facility.energy.svg', ['months'=>12, 'meter_type'=>'power']) }}" alt="Energy Trend" style="max-width:100%; height:auto; border:1px solid #eee;" />
    </div>
        <div class="mt-3">
        <a class="btn btn-sm btn-outline-primary" href="/facility/reports">Facility Reports</a>
      </div>
    </div>
</div>
@endsection


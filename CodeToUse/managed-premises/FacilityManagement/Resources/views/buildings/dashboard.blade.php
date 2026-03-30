@extends('facility::layouts.master')
@section('content')
<div class="container py-3">
  <h2>Building: {{ $building->name }}</h2>
  <div class="row g-3">
    @php $cards = [
      ['Units', $metrics['unitCount'] ?? 0],
      ['Assets', $metrics['assetCount'] ?? 0],
      ['Occupied', $metrics['activeOcc'] ?? 0],
      ['Vacant', $metrics['vacant'] ?? 0],
      ['Inspections (next 14d)', $metrics['ins_due'] ?? 0],
      ['Docs expiring (60d)', $metrics['docs_exp'] ?? 0],
    ]; @endphp
    @foreach($cards as $c)
    <div class="col-6 col-md-3">
      <div class="card text-center p-3">
        <div class="fs-6 text-muted">{{ $c[0] }}</div>
        <div class="fs-3 fw-bold">{{ $c[1] }}</div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="card p-3 mt-3">
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Energy (12 months)</h5>
    </div>
    <div class="mt-2">
      @php
        $w = 480; $h = 120; $pad = 10;
        $data = array_map(fn($r) => $r[1], array_slice($metrics['energy'] ?? [], 1));
        $max = max($data ?: [1]); $min = min($data ?: [0]); $spread = max(1e-9, $max-$min);
        $step = count($data) > 1 ? ($w - 2*$pad)/(count($data)-1) : 0;
        $pts = [];
        foreach($data as $i=>$v){
          $x = $pad + $i*$step;
          $y = $h - $pad - (($v - $min)/$spread) * ($h - 2*$pad);
          $pts[] = $x.','.$y;
        }
        $points = implode(' ', $pts);
      @endphp
      <svg xmlns="http://www.w3.org/2000/svg" width="{{ $w }}" height="{{ $h }}">
        <rect x="0" y="0" width="{{ $w }}" height="{{ $h }}" fill="white" stroke="#ddd" />
        <polyline fill="none" stroke="#07c" stroke-width="2" points="{{ $points }}" />
      </svg>
    </div>
  </div>
</div>
@endsection

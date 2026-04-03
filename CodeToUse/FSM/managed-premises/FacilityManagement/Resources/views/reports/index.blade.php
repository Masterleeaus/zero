@extends('facility::layouts.master')
@section('content')
<div class="container py-3">
  <h2>Facility Reports</h2>
  <div class="list-group">
    <a class="list-group-item list-group-item-action" href="{{ route('facility.reports.buildingEnergyCsv',['months'=>12,'meter_type'=>'power']) }}">
      Building Energy (last 12 months, power) — CSV
    </a>
    <a class="list-group-item list-group-item-action" href="{{ route('facility.reports.slaCsv') }}">
      Inspection SLA by month — CSV
    </a>
    <a class="list-group-item list-group-item-action" href="{{ route('facility.reports.occupancyCsv') }}">
      Building Occupancy Rate — CSV
    </a>
  </div>
</div>
@endsection

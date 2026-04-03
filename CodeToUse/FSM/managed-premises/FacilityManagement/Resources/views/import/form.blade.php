@extends('facility::layouts.master')
@section('content')
<div class="container py-3">
  <h2>Facility CSV Import</h2>
  @if(session('status')) <div class="alert alert-info">{{ session('status') }}</div> @endif
  <form method="post" action="{{ route('facility.import.upload') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label class="form-label">Entity</label>
      <select name="entity" class="form-select" required>
        <option value="sites">Sites</option>
        <option value="buildings">Buildings</option>
        <option value="unit_types">Unit Types</option>
        <option value="units">Units</option>
        <option value="assets">Assets</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">CSV File</label>
      <input type="file" name="file" accept=".csv,text/csv" class="form-control" required />
      <div class="form-text">Headers expected per entity:
        <ul>
          <li><b>sites</b>: code,name,address</li>
          <li><b>buildings</b>: code,site_code,name,address</li>
          <li><b>unit_types</b>: code,name,description</li>
          <li><b>units</b>: code,building_code,unit_type_code,name,floor,status</li>
          <li><b>assets</b>: label,unit_code,asset_type,serial_no,status</li>
        </ul>
      </div>
    </div>
    <button class="btn btn-primary">Upload & Import</button>
  </form>
</div>
@endsection

@extends('titantalk::layouts.base')
@section('title','Create Channel')
@section('body')
<form method="POST" action="{{ route('titantalk.channels.store') }}">@csrf
  <div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
  <div class="mb-3"><label>Driver</label>
    <select name="driver" class="form-control">
      <option value="web">web</option>
      <option value="whatsapp">whatsapp</option>
      <option value="telegram">telegram</option>
      <option value="email">email</option>
      <option value="sms">sms</option>
    </select>
  </div>
  <div class="mb-3"><label>Config (JSON)</label><textarea name="config" class="form-control" rows="5" placeholder='{"token":"...","from":"..."}'></textarea></div>
  <div class="form-check mb-3"><input type="checkbox" class="form-check-input" name="enabled" checked> <label class="form-check-label">Enabled</label></div>
  <button class="btn btn-primary">Save</button>
</form>
@endsection

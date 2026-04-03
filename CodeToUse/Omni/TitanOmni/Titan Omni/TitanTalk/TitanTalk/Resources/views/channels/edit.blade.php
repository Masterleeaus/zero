@extends('titantalk::layouts.base')
@section('title','Edit Channel')
@section('body')
<form method="POST" action="{{ route('titantalk.channels.update',$channel->id) }}">@csrf @method('PUT')
  <div class="mb-3"><label>Name</label><input name="name" class="form-control" value="{{ $channel->name }}" required></div>
  <div class="mb-3"><label>Driver</label>
    <select name="driver" class="form-control">
      @foreach(['web','whatsapp','telegram','email','sms'] as $d)
        <option value="{{ $d }}" {{ $channel->driver===$d?'selected':'' }}>{{ $d }}</option>
      @endforeach
    </select>
  </div>
  <div class="mb-3"><label>Config (JSON)</label><textarea name="config" class="form-control" rows="5">{{ json_encode($channel->config, JSON_PRETTY_PRINT) }}</textarea></div>
  <div class="form-check mb-3"><input type="checkbox" class="form-check-input" name="enabled" {{ $channel->enabled?'checked':'' }}> <label class="form-check-label">Enabled</label></div>
  <button class="btn btn-primary">Save</button>
</form>
@endsection

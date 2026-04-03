@extends('titantalk::layouts.base')
@section('title','Training Phrases')
@section('body')
<form method="POST" action="{{ route('titantalk.training.store') }}" class="mb-3">@csrf
  <div class="row g-2">
    <div class="col-md-3">
      <select name="intent_id" class="form-control" required>
        @foreach($intents as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach
      </select>
    </div>
    <div class="col-md-7"><input name="text" class="form-control" placeholder="Training phrase…" required></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
  </div>
</form>
<a href="{{ route('titantalk.training.export') }}" class="btn btn-secondary mb-3">Export All</a>
<table class="table table-sm"><thead><tr><th>ID</th><th>Intent</th><th>Phrase</th><th></th></tr></thead><tbody>
@foreach($phrases as $p)
<tr><td>{{ $p->id }}</td><td>{{ optional($p->intent)->name }}</td><td>{{ $p->text }}</td>
<td>
  <form action="{{ route('titantalk.training.delete',$p->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger">Delete</button>
  </form>
</td></tr>
@endforeach
</tbody></table>
{{ $phrases->links() }}
@endsection

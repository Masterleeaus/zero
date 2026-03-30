@extends('facility::layouts.master')
@section('content')
<div class="container py-3">
  <h2>Docs</h2>
  <a href="{ route('facility.docs.create') }" class="btn btn-primary mb-2">Create</a>
  <div class="card p-3">
    @if(isset($items) && count($items))
      <table class="table table-sm">
        <thead><tr><th>ID</th><th>Name/Label</th><th>Updated</th><th></th></tr></thead>
        <tbody>
        @foreach($items as $item)
          <tr>
            <td>{ $item->id }</td>
            <td>{ $item->name ?? $item->label ?? $item->code ?? '—' }</td>
            <td>{ $item->updated_at ?? '' }</td>
            <td><a data-id="{ $item->id }" href="/docs/{ $item->id }">View</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
      { $items->links() }
    @else
      <em>No records yet.</em>
    @endif
  </div>
</div>
@endsection

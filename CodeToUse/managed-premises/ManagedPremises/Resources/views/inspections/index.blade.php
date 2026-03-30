@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('managedpremises::app.inspections') }}</h3>
  <a class="btn btn-primary" href="{{ route('managedpremises.inspections.create', $property) }}">{{ __('managedpremises::app.add') }}</a>
  <div class="card mt-3"><div class="card-body">
    <table class="table">
      <thead><tr><th>{{ __('managedpremises::app.type') }}</th><th>{{ __('managedpremises::app.scheduled_for') }}</th><th>{{ __('managedpremises::app.status') }}</th><th></th></tr></thead>
      <tbody>
      @foreach($inspections as $i)
        <tr>
          <td>{{ $i->inspection_type }}</td>
          <td>{{ optional($i->scheduled_for)->format('Y-m-d H:i') }}</td>
          <td>{{ $i->status }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('managedpremises.inspections.edit', [$property,$i]) }}">{{ __('managedpremises::app.edit') }}</a>
            <form class="d-inline" method="POST" action="{{ route('managedpremises.inspections.destroy', [$property,$i]) }}">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">{{ __('managedpremises::app.delete') }}</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{ $inspections->links() }}
  </div></div>
</div>
@endsection

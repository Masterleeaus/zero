@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.visits') }}</h3>
  <a class="btn btn-primary" href="{{ route('propertymanagement.visits.create', $property) }}">{{ __('propertymanagement::app.add') }}</a>
  <div class="card mt-3"><div class="card-body">
    <table class="table">
      <thead><tr><th>{{ __('propertymanagement::app.type') }}</th><th>{{ __('propertymanagement::app.scheduled_for') }}</th><th>{{ __('propertymanagement::app.status') }}</th><th></th></tr></thead>
      <tbody>
      @foreach($visits as $v)
        <tr>
          <td>{{ $v->visit_type }}</td>
          <td>{{ optional($v->scheduled_for)->format('Y-m-d H:i') }}</td>
          <td>{{ $v->status }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('propertymanagement.visits.edit', [$property,$v]) }}">{{ __('propertymanagement::app.edit') }}</a>
            <form class="d-inline" method="POST" action="{{ route('propertymanagement.visits.destroy', [$property,$v]) }}">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">{{ __('propertymanagement::app.delete') }}</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{ $visits->links() }}
  </div></div>
</div>
@endsection

@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.service_plans') }}</h3>
  <a class="btn btn-primary" href="{{ route('propertymanagement.service-plans.create', $property) }}">{{ __('propertymanagement::app.add') }}</a>
  <div class="card mt-3"><div class="card-body">
    <table class="table">
      <thead><tr><th>{{ __('propertymanagement::app.name') }}</th><th>{{ __('propertymanagement::app.type') }}</th><th>{{ __('propertymanagement::app.active') }}</th><th></th></tr></thead>
      <tbody>
      @foreach($plans as $p)
        <tr>
          <td>{{ $p->name }}</td>
          <td>{{ $p->service_type }}</td>
          <td>{{ $p->is_active ? __('propertymanagement::app.yes') : __('propertymanagement::app.no') }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('propertymanagement.service-plans.edit', [$property,$p]) }}">{{ __('propertymanagement::app.edit') }}</a>
            <form class="d-inline" method="POST" action="{{ route('propertymanagement.service-plans.destroy', [$property,$p]) }}">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">{{ __('propertymanagement::app.delete') }}</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{ $plans->links() }}
  </div></div>
</div>
@endsection

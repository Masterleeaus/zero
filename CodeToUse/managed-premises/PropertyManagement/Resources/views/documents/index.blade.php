@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.documents') }}</h3>
  <a class="btn btn-primary" href="{{ route('propertymanagement.documents.create', $property) }}">{{ __('propertymanagement::app.add') }}</a>
  <div class="card mt-3"><div class="card-body">
    <table class="table">
      <thead><tr><th>{{ __('propertymanagement::app.name') }}</th><th>{{ __('propertymanagement::app.type') }}</th><th></th></tr></thead>
      <tbody>
      @foreach($docs as $d)
        <tr>
          <td>{{ $d->name }}</td>
          <td>{{ $d->doc_type }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('propertymanagement.documents.download', [$property,$d]) }}">{{ __('propertymanagement::app.download') }}</a>
            <form class="d-inline" method="POST" action="{{ route('propertymanagement.documents.destroy', [$property,$d]) }}">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">{{ __('propertymanagement::app.delete') }}</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{ $docs->links() }}
  </div></div>
</div>
@endsection

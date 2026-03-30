@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.approvals') }}</h3>
  <a class="btn btn-primary" href="{{ route('propertymanagement.approvals.create', $property) }}">{{ __('propertymanagement::app.add') }}</a>
  <div class="card mt-3"><div class="card-body">
    <table class="table">
      <thead><tr><th>{{ __('propertymanagement::app.subject') }}</th><th>{{ __('propertymanagement::app.status') }}</th><th></th></tr></thead>
      <tbody>
      @foreach($approvals as $a)
        <tr>
          <td>{{ $a->subject }}</td>
          <td>{{ $a->status }}</td>
          <td class="text-end">
            @if($a->status === 'pending')
              <form class="d-inline" method="POST" action="{{ route('propertymanagement.approvals.decide', [$property,$a]) }}">
                @csrf
                <input type="hidden" name="decision" value="approved">
                <button class="btn btn-sm btn-outline-success">{{ __('propertymanagement::app.approve') }}</button>
              </form>
              <form class="d-inline" method="POST" action="{{ route('propertymanagement.approvals.decide', [$property,$a]) }}">
                @csrf
                <input type="hidden" name="decision" value="rejected">
                <button class="btn btn-sm btn-outline-danger">{{ __('propertymanagement::app.reject') }}</button>
              </form>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{ $approvals->links() }}
  </div></div>
</div>
@endsection

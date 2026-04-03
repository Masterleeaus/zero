@extends('layouts.app')
@section('content')
<div class="content-wrapper">
  <div class="d-flex justify-content-between mb-3">
    <h4>@lang('propertymanagement::app.service_windows') - {{ $property->name }}</h4>
    <a href="{{ route('propertymanagement.properties.show', $property->id) }}" class="btn btn-secondary btn-sm">@lang('app.back')</a>
  </div>

  @include('propertymanagement::partials.property-tabs', ['property'=>$property])

  <x-card>
    <x-slot name="header">@lang('propertymanagement::app.service_windows')</x-slot>
    <x-slot name="body">
      <form id="pmForm" method="POST" action="{{ $storeUrl }}">
        @csrf
        <div class="row">
          <div class="col-md-4"><x-forms.text fieldName="days" fieldId="days" fieldLabel="@lang('propertymanagement::app.days')" fieldPlaceholder="Mon,Tue,Wed" /></div>
          <div class="col-md-3"><x-forms.text fieldName="time_from" fieldId="time_from" fieldLabel="@lang('propertymanagement::app.from')" fieldPlaceholder="09:00" /></div>
          <div class="col-md-3"><x-forms.text fieldName="time_to" fieldId="time_to" fieldLabel="@lang('propertymanagement::app.to')" fieldPlaceholder="17:00" /></div>
          <div class="col-md-12 mt-2"><x-forms.textarea fieldName="notes" fieldId="notes" fieldLabel="@lang('propertymanagement::app.notes')" /></div>
        </div>
        <x-forms.button-primary class="mt-3" id="saveBtn">@lang('app.save')</x-forms.button-primary>
      </form>

      <hr>

      <div class="table-responsive">
        <table class="table table-bordered">
          <thead><tr><th>@lang('propertymanagement::app.days')</th><th>@lang('propertymanagement::app.from')</th><th>@lang('propertymanagement::app.to')</th><th>@lang('propertymanagement::app.notes')</th><th class="text-right">@lang('app.action')</th></tr></thead>
          <tbody>
            @forelse($windows as $w)
              <tr>
                <td>{{ $w->days }}</td>
                <td>{{ $w->time_from }}</td>
                <td>{{ $w->time_to }}</td>
                <td>{{ $w->notes }}</td>
                <td class="text-right">
                  <x-forms.button-secondary data-url="{{ route('propertymanagement.properties.servicewindows.destroy', [$property->id, $w->id]) }}" class="delete-row btn-sm" icon="trash">@lang('app.delete')</x-forms.button-secondary>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted">@lang('propertymanagement::app.no_records')</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </x-slot>
  </x-card>
</div>
@endsection

@push('scripts')
<script>
$(document).on('click','#saveBtn',function(e){
  e.preventDefault();
  $.easyAjax({
    url: $('#pmForm').attr('action'),
    container: '#pmForm',
    type: "POST",
    data: $('#pmForm').serialize()
  });
});
</script>
@endpush

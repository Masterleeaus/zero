@extends('layouts.app')
@section('content')
<div class="content-wrapper">
  <div class="d-flex justify-content-between mb-3">
    <h4>@lang('propertymanagement::app.hazards') - {{ $property->name }}</h4>
    <a href="{{ route('propertymanagement.properties.show', $property->id) }}" class="btn btn-secondary btn-sm">@lang('app.back')</a>
  </div>

  @include('propertymanagement::partials.property-tabs', ['property'=>$property])

  <x-card>
    <x-slot name="header">@lang('propertymanagement::app.hazards')</x-slot>
    <x-slot name="body">
      <form id="pmForm" method="POST" action="{{ $storeUrl }}">
        @csrf
        <div class="row">
          <div class="col-md-6"><x-forms.text fieldName="hazard" fieldId="hazard" fieldLabel="@lang('propertymanagement::app.hazard')" /></div>
          <div class="col-md-3">
            <x-forms.select fieldName="risk_level" fieldId="risk_level" fieldLabel="@lang('propertymanagement::app.risk_level')">
              <option value="low">@lang('propertymanagement::app.low')</option>
              <option value="medium" selected>@lang('propertymanagement::app.medium')</option>
              <option value="high">@lang('propertymanagement::app.high')</option>
            </x-forms.select>
          </div>
          <div class="col-md-12 mt-2"><x-forms.textarea fieldName="controls" fieldId="controls" fieldLabel="@lang('propertymanagement::app.controls')" /></div>
        </div>
        <x-forms.button-primary class="mt-3" id="saveBtn">@lang('app.save')</x-forms.button-primary>
      </form>

      <hr>

      <div class="table-responsive">
        <table class="table table-bordered">
          <thead><tr><th>@lang('propertymanagement::app.hazard')</th><th>@lang('propertymanagement::app.risk_level')</th><th>@lang('propertymanagement::app.controls')</th><th class="text-right">@lang('app.action')</th></tr></thead>
          <tbody>
            @forelse($hazards as $h)
              <tr>
                <td>{{ $h->hazard }}</td>
                <td>{{ $h->risk_level }}</td>
                <td>{{ $h->controls }}</td>
                <td class="text-right">
                  <x-forms.button-secondary data-url="{{ route('propertymanagement.properties.hazards.destroy', [$property->id, $h->id]) }}" class="delete-row btn-sm" icon="trash">@lang('app.delete')</x-forms.button-secondary>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted">@lang('propertymanagement::app.no_records')</td></tr>
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

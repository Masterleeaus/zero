@php
    $addClientPermission = user()->permission('add_clients');
@endphp

<x-forms.label class="{{ $labelClass }}" fieldId="client_id" :fieldLabel="__('app.customer')" :fieldRequired="$fieldRequired">
</x-forms.label>

<x-forms.input-group>
    <select class="form-control select-picker" data-live-search="true" data-size="8" name="client_id"
            id="client_list_id">
        <option value="">--</option>
        @foreach ($customers as $clientOpt)
            <option @selected(!is_null($selected) && $selected == $clientOpt->id)
                    data-content="<x-customer-search-option :user='$clientOpt' />"
                    value="{{ $clientOpt->id }}">{{ $clientOpt->name }} </option>
        @endforeach
    </select>

    @if ($addClientPermission == 'all' || $addClientPermission == 'added')
        <x-slot name="append">
            <a href="javascript:;" id="quick-create-customer"
               data-toggle="tooltip" data-original-title="{{ __('modules.customer.addNewClient') }}"
               class="btn btn-outline-secondary border-grey"
               data-redirect-url="{{ url()->full() }}">@lang('app.add')</a>
        </x-slot>
    @endif
</x-forms.input-group>

<script>
    $('#quick-create-customer').click(function () {
        const url = "{{ route('customers.create') . '?quick-form=1' }}";
        $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_DEFAULT, url);
    });
</script>

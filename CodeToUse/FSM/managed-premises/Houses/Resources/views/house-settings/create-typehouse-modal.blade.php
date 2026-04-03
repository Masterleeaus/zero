<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.add') @lang('houses::modules.house.typehouse')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="addTicketChannel" method="POST" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="typehouse_code" :fieldLabel="__('houses::modules.house.typehouseCode')"
                            fieldName="typehouse_code" fieldRequired="true" fieldPlaceholder="e.g. 1BR, 2BR, etc.">
                        </x-forms.text>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="typehouse_name" :fieldLabel="__('houses::modules.house.typehouseName')"
                            fieldName="typehouse_name" fieldRequired="true" fieldPlaceholder="e.g. One Bedroom, Two Bedrooms, etc.">
                        </x-forms.text>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-ticket-typehouse" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    // save typehouse
    $('#save-ticket-typehouse').click(function() {
        $.easyAjax({
            url: "{{ route('typehouses.store') }}",
            container: '#addTicketChannel',
            type: "POST",
            blockUI: true,
            data: $('#addTicketChannel').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    if ($('#ticket_typehouse_id').length > 0) {
                        $('#ticket_typehouse_id').html(response.optionData);
                        $('#ticket_typehouse_id').selectpicker('refresh');
                        $(MODAL_LG).modal('hide');
                    } else {
                        window.location.reload();
                    }
                }
            }
        })
    });

</script>

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.zone.addTitle')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">

    <x-form id="save-zone-data-form">
        <div class="add-customer bg-white rounded">

            <div class="row p-20">
                <div class="col-md-6">
                    <x-forms.text fieldId="designation_name" :fieldLabel="__('app.name')" fieldName="team_name"
                        fieldRequired="true" :fieldPlaceholder="__('placeholders.zone')">
                    </x-forms.text>
                </div>
                <div class="col-md-6">
                    <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('app.parentId')" fieldName="parent_label">
                    </x-forms.label>
                    <x-forms.input-group>
                        <select class="form-control select-picker mt" name="parent_id" id="parent_id"
                            data-live-search="true">
                            <option value="">--</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->team_name }}</option>
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            </div>


        </div>
    </x-form>


</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-zone-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(document).ready(function() {
        $(".select-picker").selectpicker();
    });

    $('#save-zone-form').click(function() {
        var url = "{{ route('zones.store') }}";
        $.easyAjax({
            url: url,
            container: '#save-zone-data-form',
            type: "POST",
            data: $('#save-zone-data-form').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-category",
            success: function(response) {
                if (response.status == 'success') {
                    var options = [];
                    var rData = [];
                    rData = response.zones;

                    $.each(rData, function(index, value) {
                        var selectData = '<option value="">--</option>';
                        selectData = '<option value="' + value.id + '">' + value.team_name + '</option>';
                        options.push(selectData);
                    });

                    $('#employee_department').html(options);
                    $('#employee_department').selectpicker('refresh');
                    $(MODAL_LG).modal('hide');
                    
                }
            }
        })
    });
</script>

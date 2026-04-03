<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.menu.addDesignation')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    
            <x-form id="save-role-data-form">
                <div class="add-customer bg-white rounded">
                    
                    <div class="row p-20">
                        <div class="col-md-6">
                            <x-forms.text fieldId="designation_name" :fieldLabel="__('app.name')" fieldName="name"
                                          fieldRequired="true" :fieldPlaceholder="__('placeholders.role')">
                            </x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('app.menu.parent_id')"
                                           fieldName="parent_label">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="parent_id" id="parent_id"
                                        data-live-search="true">
                                    <option value="">--</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
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
    <x-forms.button-primary id="save-role-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
     $(document).ready(function () {
        $(".select-picker").selectpicker();
        $('#save-role-form').click(function () {

            const url = "{{ route('roles.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-role-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-role-form",
                data: $('#save-role-data-form').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        var options = [];
                        var rData = [];
                        rData = response.roles;

                        $.each(rData, function(index, value) {
                            var selectData = '<option value="">--</option>';
                            selectData = '<option value="' + value.id + '">' + value.name + '</option>';
                            options.push(selectData);
                        });

                        if ($(MODAL_LG).hasClass('show')) {
                            $(MODAL_LG).modal('hide');
                        }

                        $('#employee_designation').html(options);
                        $('#employee_designation').selectpicker('refresh');
                    }
                }
            });
        });

        init(RIGHT_MODAL);
});

</script>

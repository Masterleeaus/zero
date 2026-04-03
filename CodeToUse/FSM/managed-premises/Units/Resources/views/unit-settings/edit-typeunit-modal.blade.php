<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.update') @lang('units::modules.unit.typeunit')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="editTypeUnit" method="PUT" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="typeunit_code" :fieldLabel="__('units::modules.unit.typeunitCode')"
                            fieldName="typeunit_code" fieldRequired="true" fieldPlaceholder="e.g. 01, 02, etc." :fieldValue="$typeunit->typeunit_code">
                        </x-forms.text>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="typeunit_name" :fieldLabel="__('units::modules.unit.typeunitName')"
                            fieldName="typeunit_name" fieldRequired="true" fieldPlaceholder="e.g. First TypeUnit, Scond TypeUnit, etc." :fieldValue="$typeunit->typeunit_name">
                        </x-forms.text>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-unit-typeunit" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    // save unit channnel
    $('#save-unit-typeunit').click(function () {
        $.easyAjax({
            url: "{{route('typeunits.update', $typeunit->id)}}",
            container: '#editTypeUnit',
            type: "POST",
            blockUI: true,
            data: $('#editTypeUnit').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    });
</script>

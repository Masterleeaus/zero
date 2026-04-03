<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.update') @lang('units::modules.unit.tower')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="editTower" method="PUT" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="tower_code" :fieldLabel="__('units::modules.unit.towercode')"
                            fieldName="tower_code" fieldRequired="true" fieldPlaceholder="e.g. 01, 02, etc." :fieldValue="$tower->tower_code">
                        </x-forms.text>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="tower_name" :fieldLabel="__('units::modules.unit.towerName')"
                            fieldName="tower_name" fieldRequired="true" fieldPlaceholder="e.g. First Tower, Scond Tower, etc." :fieldValue="$tower->tower_name">
                        </x-forms.text>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-unit-tower" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    // save unit channnel
    $('#save-unit-tower').click(function () {
        $.easyAjax({
            url: "{{route('towers.update', $tower->id)}}",
            container: '#editTower',
            type: "POST",
            blockUI: true,
            data: $('#editTower').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    });
</script>

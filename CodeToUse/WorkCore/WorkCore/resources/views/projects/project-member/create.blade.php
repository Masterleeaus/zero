<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.sites.addMemberTitle')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="addProjectMemberForm">
        <input type="hidden" name="project_id" value="{{ $projectId }}">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-3">
                    <div class="d-flex">
                        <x-forms.radio fieldId="member-cleaner" :fieldLabel="__('modules.sites.chooseMembers')"
                            fieldName="choose_type" fieldValue="cleaner" checked="true">
                        </x-forms.radio>
                        <x-forms.radio fieldId="member-zone" :fieldLabel="__('modules.sites.chooseDepartment')"
                            fieldValue="zone" fieldName="choose_type"></x-forms.radio>
                    </div>
                </div>
            </div>

            <div class="col-md-12" id="select-cleaner-section">
                <div class="form-group my-3">
                    <x-forms.label fieldId="selectEmployee" :fieldLabel="__('modules.sites.addMemberTitle')"
                        fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <select class="form-control multiple-users" multiple name="user_id[]" data-live-search="true" data-size="8">
                            @foreach ($cleaners as $item)
                                <x-user-option :user="$item" :pill="true" />
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            </div>
            <div class="col-md-12 d-none" id="select-zone-section">
                <div class="form-group my-3">
                    <x-forms.label fieldId="selectDepartment" :fieldLabel="__('app.add') .' '. __('app.team')"
                        fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <select class="form-control multiple-users" multiple name="group_id[]" data-live-search="true"
                            data-size="8">
                            @foreach ($groups as $group)
                                <option
                                    data-content="<span class='badge badge-pill badge-light border p-2'>{{ $group->team_name }}</span>"
                                    value="{{ $group->id }}">{{ $group->team_name }} </option>
                            @endforeach
                        </select>
                    </x-forms.input-group>
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-site-member" icon="check">@lang('app.save')</x-forms.button-primary>
    <x-forms.button-primary id="save-site-zone" class="d-none" icon="check">@lang('app.save')
    </x-forms.button-primary>
</div>

<script>
    $('input[type=radio][name=choose_type]').change(function() {
        $('#select-cleaner-section').toggleClass('d-none');
        $('#select-zone-section').toggleClass('d-none');

        $('#save-site-member').toggleClass('d-none');
        $('#save-site-zone').toggleClass('d-none');
    });

    $('#save-site-member').click(function() {
        var url = "{{ route('site-members.store') }}";
        $.easyAjax({
            url: url,
            container: '#addProjectMemberForm',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: "#save-site-member",
            data: $('#addProjectMemberForm').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });

    $('#save-site-zone').click(function() {
        var url = "{{ route('site-members.store_group') }}";
        $.easyAjax({
            url: url,
            container: '#addProjectMemberForm',
            blockUI: true,
            type: "POST",
            data: $('#addProjectMemberForm').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });

    $("#addProjectMemberForm .multiple-users").selectpicker({
        actionsBox: true,
        selectAllText: "{{ __('modules.permission.selectAll') }}",
        deselectAllText: "{{ __('modules.permission.deselectAll') }}",
        multipleSeparator: " ",
        selectedTextFormat: "count > 8",
        countSelectedText: function(selected, total) {
            return selected + " {{ __('app.membersSelected') }} ";
        }
    });

</script>

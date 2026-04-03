<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.sites.createMilestone')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<x-form id="addProjectMilestoneForm">
    <div class="modal-body">
        <input type="hidden" name="project_id" value="{{ $site->id }}">
        <input type="hidden" name="currency_id" value="{{ $site->currency_id ?? company()->currency_id }}">
        <div class="row">
            <div class="col-md-6">
                <x-forms.text fieldId="milestone_title" :fieldLabel="__('modules.sites.milestoneTitle')"
                    fieldName="milestone_title" fieldRequired="true" :fieldPlaceholder="__('placeholders.milestone')">
                </x-forms.text>
            </div>
            <div class="col-md-6">
                <x-forms.number fieldId="cost" :fieldLabel="__('modules.sites.milestoneCost')" fieldName="cost"
                    :fieldPlaceholder="__('placeholders.price')">
                    </x-forms.number>
            </div>
            <div class="col-md-6">
                <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                    <option value="incomplete">@lang('app.incomplete')</option>
                    <option value="complete">@lang('app.complete')</option>
                </x-forms.select>
            </div>
            <div class="col-md-6">
                <x-forms.select fieldId="add_to_budget" :fieldLabel="__('modules.sites.addCostProjectBudget')"
                    fieldName="add_to_budget">
                    <option value="no">@lang('app.no')</option>
                    <option value="yes">@lang('app.yes')</option>
                </x-forms.select>
            </div>
            <div class="col-md-12">
                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.sites.milestoneSummary')"
                    fieldName="summary" fieldId="summary" fieldRequired="true"
                    :fieldPlaceholder="__('placeholders.milestoneSummary')">
                </x-forms.textarea>
            </div>

            <div class="col-md-6">
                <x-forms.datepicker fieldId="start_date"
                    :fieldLabel="__('modules.timeLogs.startDate')" fieldName="start_date"
                    :fieldValue="now(company()->timezone)->format(company()->date_format)"
                    :fieldPlaceholder="__('placeholders.date')" />
            </div>

            <div class="col-md-6">
                <x-forms.datepicker fieldId="end_date"
                    :fieldLabel="__('modules.timeLogs.endDate')" fieldName="end_date"
                    :fieldValue="now(company()->timezone)->format(company()->date_format)"
                    :fieldPlaceholder="__('placeholders.date')" />
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
        <x-forms.button-primary id="save-site-milestone" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>

</x-form>

<script>

$(document).ready(function() {
    $("#addProjectMilestoneForm .select-picker").selectpicker();

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                if (typeof dp2.dateSelected !== 'undefined' && dp2.dateSelected.getTime() < date
                    .getTime()) {
                    dp2.setDate(date, true)
                }
                if (typeof dp2.dateSelected === 'undefined') {
                    dp2.setDate(date, true)
                }
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#end_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });
    });
    $('#save-site-milestone').click(function() {
        var url = "{{ route('milestones.store') }}";
        $.easyAjax({
            url: url,
            container: '#addProjectMilestoneForm',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: '#save-site-milestone',
            data: $('#addProjectMilestoneForm').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        })
    });
</script>

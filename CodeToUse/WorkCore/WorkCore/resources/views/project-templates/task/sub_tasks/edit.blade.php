<x-form id="edit-save-checklist-data-form" method="PUT">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('modules.service jobs.subTask')</h5>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">

        <input type="hidden" name="task_id" value="{{ $subTask->task_id }}">
        <div class="row">
            <div class="col-md-12">
                <x-forms.text :fieldLabel="__('app.title')" fieldName="title" fieldRequired="true" fieldId="title"
                    :fieldValue="$subTask->title" :fieldPlaceholder="__('placeholders.service job')" />
            </div>

            <div class="col-md-12">
                <x-forms.datepicker fieldId="edit_task_due_date" :fieldLabel="__('app.dueDate')" fieldName="due_date"
                    :fieldValue="$subTask->due_date ? $subTask->due_date->format(company()->date_format) : ''"
                    :fieldPlaceholder="__('placeholders.date')" />
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="edit-save-checklist" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>
<script>
    $(document).ready(function() {

        datepicker('#edit_task_due_date', {
            position: 'bl',
            dateSelected: new Date("{{ ($subTask->due_date ? str_replace('-', '/', $subTask->due_date) : str_replace('-', '/', now(company()->timezone))) }}"),
            ...datepickerConfig
        });

        $('#edit-save-checklist').click(function() {

            const url = "{{ route('sub-service jobs.update', $subTask->id) }}";

            $.easyAjax({
                url: url,
                container: '#edit-save-checklist-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#edit-save-checklist",
                data: $('#edit-save-checklist-data-form').serialize(),
                success: function(response) {
                    if (response.status == "success") {
                        $('#sub-service job-list').html(response.view);
                        $(MODAL_LG).modal('hide');
                    }

                }
            });
        });

    });

</script>

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">

    <div class="p-20">

        <div class="row">
            <div class="col-md-12">
                <a class="f-15 f-w-500" href="javascript:;" id="add-sub-service job"><i
                        class="icons icon-plus font-weight-bold mr-1"></i>@lang('app.menu.addSubTask')
                    </a>
            </div>
        </div>

        <x-form id="save-checklist-data-form" class="d-none">
            <input type="hidden" name="task_id" value="{{ $service job->id }}">
            <div class="row">
                <div class="col-md-8">
                    <x-forms.text :fieldLabel="__('app.title')" fieldName="title" fieldRequired="true"
                        fieldId="title" :fieldPlaceholder="__('placeholders.service job')" />
                </div>
                <div class="col-md-12">
                    <div class="w-100 justify-content-end d-flex mt-2">
                        <x-forms.button-cancel id="cancel-checklist" class="border-0 mr-3">@lang('app.cancel')
                        </x-forms.button-cancel>
                        <x-forms.button-primary id="save-checklist" icon="location-arrow">@lang('app.submit')
                            </x-forms.button-primary>
                    </div>
                </div>
            </div>
        </x-form>
    </div>


    <div class="d-flex flex-wrap justify-content-between p-20" id="sub-service job-list">

        <x-table class="border-0 pb-3 admin-dash-table table-hover">

            <x-slot name="thead">
                <th class="pl-20">#</th>
                <th>@lang('app.name')</th>
                <th class="text-right pr-20">@lang('app.action')</th>
            </x-slot>

            @forelse ($service job->checklists as $key => $checklist)
                <tr id="row-{{ $checklist->id }}">
                    <td class="pl-20">{{ $key + 1 }}</td>
                    <td>
                        {{$checklist->title}}
                    </td>

                    <td class="text-right pr-20">
                        <x-forms.button-secondary data-row-id="{{ $checklist->id }}" icon="trash"
                                                  class="delete-checklist">
                            @lang('app.delete')</x-forms.button-secondary>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-cards.no-record icon="service jobs" :message="__('team chat.noSubTaskFound')" />
                    </td>
                </tr>
            @endforelse
        </x-table>

    </div>

</div>
<!-- TAB CONTENT END -->

<script>
    $(document).ready(function() {

        $('#save-checklist').click(function() {

            const url = "{{ route('site-template-sub-service job.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-checklist-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-checklist",
                data: $('#save-checklist-data-form').serialize(),
                success: function(response) {
                    if (response.status == "success") {
                        window.location.reload();
                    }

                }
            });
        });

        $('body').on('click', '#add-sub-service job', function() {
            $(this).closest('.row').addClass('d-none');
            $('#save-checklist-data-form').removeClass('d-none');
        });


        $('#cancel-checklist').click(function() {
            $('#save-checklist-data-form').addClass('d-none');
            $('#add-sub-service job').closest('.row').removeClass('d-none');
        });


    });

</script>

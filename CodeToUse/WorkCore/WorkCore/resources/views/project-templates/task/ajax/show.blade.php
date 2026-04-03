<div id="service job-detail-section">

    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="heading-h1 mb-3">{{ $service job->heading }}</h3>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="dropdown">
                                <button
                                    class="btn btn-lg f-14 px-2 py-1 text-dark-grey  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                     aria-labelledby="dropdownMenuLink" tabindex="0">

                                    <a class="cursor-pointer d-block text-dark-grey f-13 px-3 py-2 openRightModal"
                                       href="{{ route('site-template-service job.edit', $service job->id) }}">@lang('app.edit')
                                        @lang('app.service job')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="col-12 px-0 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">@lang('app.site')</p>
                        <p class="mb-0 text-dark-grey f-14">
                            @if ($service job->project_template_id)
                                    {{ $service job->projectTemplate->project_name }}
                            @else
                                --
                            @endif
                        </p>

                    </div>
                    <div class="col-12 px-0 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                            @lang('modules.service jobs.priority')</p>
                        <p class="mb-0 text-dark-grey f-14">
                            @if ($service job->priority == 'high')
                                <i class="fa fa-circle mr-1 text-red f-10"></i>
                            @elseif ($service job->priority == 'medium')
                                <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                            @else
                                <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                            @endif
                            @lang($service job->priority)
                        </p>
                    </div>

                    <div class="col-12 px-0 pb-3 d-flex">
                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                            @lang('modules.service jobs.assignTo')</p>
                        <p class="mb-0 text-dark-grey f-14">
                        @foreach ($service job->usersMany as $item)
                            <div class="taskEmployeeImg rounded-circle mr-1">
                                <a href="{{ route('cleaners.show', $item->id) }}">
                                    <img data-toggle="tooltip" data-original-title="{{ $item->name }}"
                                         src="{{ $item->image_url }}">
                                </a>
                            </div>
                            @endforeach
                            </p>
                    </div>

                    <x-cards.data-row :label="__('modules.service jobs.taskCategory')"
                                      :value="$service job->category->category_name ?? '--'" html="true" />
                    <x-cards.data-row :label="__('modules.sites.milestones')"
                                      :value="$service job->milestone->milestone_title ?? '--'" html="true" />
                    <x-cards.data-row :label="__('app.description')" :value="$service job->description" html="true" />

                    @if (($taskSettings->label == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()))
                        @php
                            // Convert task_labels string to an array of IDs
                            $taskLabelIds = explode(',', $service job->task_labels);
                        @endphp
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('app.label')</p>
                            <p class="mb-0 text-dark-grey f-14 w-70">
                                @forelse ($taskLabelList->filter(function ($label) use ($taskLabelIds) {
                                    return in_array($label->id, $taskLabelIds);
                                }) as $key => $label)
                                    <span class='badge badge-secondary'
                                          style='background-color: {{ $label->label_color }}'
                                          @if ($label->description)
                                                data-toggle="popover"
                                                data-placement="top"
                                                data-content="{!! $label->description !!}"
                                                data-html="true"
                                                data-trigger="hover"
                                            @endif
                                          >{!! $label->label_name !!} </span>
                                @empty
                                    --
                                @endforelse
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TASK TABS START -->
            <div class="bg-additional-grey rounded my-3">

                <a class="mb-0 d-block d-lg-none text-dark-grey s-b-mob-sidebar" onclick="openSettingsSidebar()"><i
                        class="fa fa-ellipsis-v"></i></a>

                <div class="s-b-inner s-b-notifications bg-white b-shadow-4 rounded">

                    <x-tab-section class="service job-tabs">

                        <x-tab-item class="ajax-tab" :active="(request('view') === 'sub_task' || !request('view'))"
                                    link="#">
                            @lang('modules.service jobs.subTask')</x-tab-item>
                    </x-tab-section>


                    <div class="s-b-n-content">
                        <div class="tab-content" id="nav-tabContent">
                            @include($tab)
                        </div>
                    </div>
                </div>

            </div>
            <!-- TASK TABS END -->

        </div>

    </div>

    <script>
        $(document).ready(function() {

            $('body').on('click', '.delete-checklist', function() {
                var id = $(this).data('row-id');
                Swal.fire({
                    title: "@lang('team chat.sweetAlertTitle')",
                    text: "@lang('team chat.recoverRecord')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('team chat.confirmDelete')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: {
                        confirmButton: 'btn btn-primary mr-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "{{ route('site-template-sub-service job.destroy', ':id') }}";
                        url = url.replace(':id', id);

                        var token = "{{ csrf_token() }}";

                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                '_method': 'DELETE'
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    $('#sub-service job-list').html(response.view);
                                }
                            }
                        });
                    }
                });
            });

            $('body').on('click', '.edit-checklist', function() {
                var id = $(this).data('row-id');
                var url = "{{ route('site-template-sub-service job.edit', ':id') }}";
                url = url.replace(':id', id);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            init(RIGHT_MODAL);
        });

    </script>
</div>

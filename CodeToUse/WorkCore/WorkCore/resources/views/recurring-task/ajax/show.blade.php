<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('app.recurring') . ' ' . __('app.details')" class=" mt-4">
            <x-cards.data-row :label="__('modules.service jobs.repeatEvery')" :value="$service job->repeat_every . ' ' . __('app.'.$service job->repeat_type)" />
            <x-cards.data-row :label="__('modules.service jobs.cycles')" :value="$service job->repeat_cycles" />
            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                    @lang('modules.service jobs.completedTotalTask')</p>
                <p class="mb-0 text-dark-grey f-14 ">
                    {{$completedTaskCount}}/{{$service job->repeat_cycles}}
                </p>
            </div>
        </x-cards.data>
        <x-cards.data :title="__('app.service job') . ' ' . __('app.details')" class=" mt-4">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-body">

                    <x-cards.data-row :label="__('app.title')" :value="$service job->heading"
                        html="true" />

                    @php
                        if ($service job->project_id) {
                            if ($service job->site->status == 'in progress') {
                                $site = '<i class="fa fa-circle mr-1 text-blue f-10"></i>';
                            } elseif ($service job->site->status == 'on hold') {
                                $site = '<i class="fa fa-circle mr-1 text-yellow f-10"></i>';
                            } elseif ($service job->site->status == 'not started') {
                                $site = '<i class="fa fa-circle mr-1 text-yellow f-10"></i>';
                            } elseif ($service job->site->status == 'canceled') {
                                $site = '<i class="fa fa-circle mr-1 text-red f-10"></i>';
                            } elseif ($service job->site->status == 'finished') {
                                $site = '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>';
                            }
                            $site = $service job->site->project_name;

                        } else {
                            $site = '--';
                        }
                    @endphp

                    @if (($taskSettings->site == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()))
                        <x-cards.data-row :label="__('app.site')" :value="$site" html="true" />
                    @endif

                    @if (($taskSettings->priority == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()) )
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('modules.service jobs.priority')</p>
                            <p class="mb-0 text-dark-grey f-14 w-70">
                                @if ($service job->priority == 'high')
                                    <i class="fa fa-circle mr-1 text-red f-10"></i>
                                @elseif ($service job->priority == 'medium')
                                    <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                @else
                                    <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                                @endif
                                @lang('app.'.$service job->priority)
                            </p>
                        </div>
                    @endif

                    @if (($taskSettings->site == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()))
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">@lang('app.status')</p>
                            <p class="mb-0 text-dark-grey f-14 w-70">
                                <i class="fa fa-circle mr-1 f-10 {{ $service job->boardColumn->label_color }}"
                                style="color: {{ $service job->boardColumn->label_color }}"></i>{{ $service job->boardColumn->column_name }}
                            </p>
                        </div>
                    @endif

                    @if (($taskSettings->assigned_to == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()))
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('modules.service jobs.assignTo')</p>
                            @if (count($service job->users) > 0)
                                @if (count($service job->users) > 1)
                                    @foreach ($service job->users as $item)
                                        <div class="taskEmployeeImg rounded-circle mr-1">
                                            <a href="{{ route('cleaners.show', $item->id) }}">
                                                <img data-toggle="tooltip" data-original-title="{{ $item->name }}"
                                                     src="{{ $item->image_url }}">
                                            </a>
                                        </div>
                                    @endforeach
                                @else
                                    @foreach ($service job->users as $item)
                                        <x-cleaner :user="$item"/>
                                    @endforeach
                                @endif
                            @else
                                --
                            @endif
                        </div>
                    @endif

                     <x-cards.data-row :label="__('modules.sites.milestones')"
                                      :value="$service job->milestone->milestone_title ?? '--'"/>

                    @if (($taskSettings->label == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()))
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('app.label')</p>
                            <p class="mb-0 text-dark-grey f-14 w-70">
                                @forelse ($service job->labels as $key => $label)
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

                    @if (in_array('gitlab', user_modules()) && isset($gitlabIssue))
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                GitLab</p>
                            <div class="mb-0 w-70">
                                <div class='card border'>
                                    <div
                                        class="card-body bg-white d-flex justify-content-between p-2 align-items-center rounded">
                                        <h4 class="f-13 f-w-500 mb-0">
                                            <img src="{{ asset('img/gitlab-icon-rgb.png') }}" class="height-35">
                                            <a href="{{ $gitlabIssue['web_url'] }}" class="text-darkest-grey f-w-500"
                                               target="_blank">#{{ $gitlabIssue['iid'] }} {{ $gitlabIssue['title'] }} <i
                                                    class="fa fa-external-link-alt"></i></a>
                                        </h4>
                                        <div>
                                            <span
                                                class="badge badge-{{ $gitlabIssue['state'] == 'opened' ? 'danger' : 'success' }}">{{ $gitlabIssue['state'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (($taskSettings->task_category == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()))
                        <x-cards.data-row :label="__('modules.service jobs.taskCategory')"
                                          :value="$service job->category->category_name ?? '--'" html="true"/>
                    @endif

                    @if (($taskSettings->description == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()))
                        <x-cards.data-row :label="__('app.description')"
                                          :value="!empty($service job->description) ? $service job->description : '--'"
                                          html="true"/>
                    @endif

                    {{-- Custom fields data --}}
                    @if (($taskSettings->custom_fields == 'yes' && in_array('customer', user_roles())) || in_array('admin', user_roles()) || in_array('cleaner', user_roles()))
                        <x-forms.custom-field-show :fields="$fields" :model="$service job"></x-forms.custom-field-show>
                    @endif

                </div>
            </div>
        </x-cards.data>
    </div>
</div>


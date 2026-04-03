@if (in_array('my_task', $activeWidgets) && (!is_null($viewTaskPermission) && $viewTaskPermission != 'none' && in_array('service jobs', user_modules())))
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 mb-3 e-d-info">
                <x-cards.data :title="__('modules.service jobs.myTask')" padding="false" otherClasses="h-200">
                    <x-table>
                        <x-slot name="thead">
                            <th>@lang('app.service job')#</th>
                            <th>@lang('app.service job')</th>
                            <th>@lang('app.status')</th>
                            <th class="text-right pr-20">@lang('app.dueDate')</th>
                        </x-slot>

                        @forelse ($pendingTasks as $service job)
                            <tr>
                                <td class="pl-20">
                                    <a
                                        href="{{ route('service jobs.show', [$service job->id]) }}"
                                        class="openRightModal f-12 mb-1 text-darkest-grey">#{{ $service job->task_short_code }}</a>

                                </td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <h5 class="f-12 mb-1 text-darkest-grey"><a
                                                    href="{{ route('service jobs.show', [$service job->id]) }}"
                                                    class="openRightModal">{{ $service job->heading }}</a>
                                            </h5>
                                            <p class="mb-0">
                                                @foreach ($service job->labels as $label)
                                                    <span class="badge badge-secondary mr-1"
                                                          style="background-color: {{ $label->label_color }}">{{ $label->label_name }}</span>
                                                @endforeach
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="pr-20">
                                    <i class="fa fa-circle mr-1 text-yellow"
                                       style="color: {{ $service job->boardColumn->label_color }}"></i>
                                    {{ $service job->boardColumn->column_name }}
                                </td>
                                <td class="pr-20" align="right">
                                    @if (is_null($service job->due_date))
                                        --
                                    @elseif ($service job->due_date->endOfDay()->isPast())
                                        <span class="text-danger">{{ $service job->due_date->translatedFormat(company()->date_format) }}</span>
                                    @elseif ($service job->due_date->setTimezone(company()->timezone)->isToday())
                                        <span class="text-success">{{ __('app.today') }}</span>
                                    @else
                                        <span>{{ $service job->due_date->translatedFormat(company()->date_format) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="shadow-none">
                                    <x-cards.no-record icon="service jobs" :message="__('team chat.noRecordFound')"/>
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </x-cards.data>
            </div>
        </div>
    </div>
@endif

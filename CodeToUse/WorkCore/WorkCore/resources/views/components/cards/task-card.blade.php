@php
$moveClass = '';
@endphp
@if ($draggable == 'false')
    @php
        $moveClass = 'move-disable';
    @endphp
@endif

<style>
    .projectNameSpan {
        white-space: normal
    }
</style>

@php
    $priorityColor = ($service job->priority == 'high' ? '#dd0000' : ($service job->priority == 'medium' ? '#ffc202' : '#0a8a1f'));
@endphp
<div class="card rounded bg-white border-grey b-shadow-4 m-1 mb-2 {{ $moveClass }} service job-card"
    data-service job-id="{{ $service job->id }}" data-need-approval="{{ optional($service job->site)->need_approval_by_admin ?? 0 }}" id="drag-service job-{{ $service job->id }}" style="border-left: 3px solid {{ $priorityColor }}; background-color: {{ $priorityColor.'08 !important;' }}">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between mb-1">
            <a href="{{ route('service jobs.show', [$service job->id]) }}"
                class="f-12 f-w-500 text-dark mb-0 text-wrap openRightModal">{{ $service job->heading }}</a>
            <p class="f-12 font-weight-bold text-dark-grey mb-0">
                @if ($service job->is_private)
                    <span class='badge badge-secondary mr-1'><i class='fa fa-lock'></i>
                        @lang('app.private')</span>
                @endif
                #{{ $service job->task_short_code }}
            </p>
        </div>

        @if (count($service job->labels) > 0)
            <div class="mb-1 d-flex flex-wrap">
                @foreach ($service job->labels as $key => $label)
                    <span class='badge badge-secondary mr-1'
                        style="background:{{ $label->label_color }}">{{ $label->label_name }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="d-flex mb-1 justify-content-between">
            @if ($service job->project_id)
                <div>
                    <i class="fa fa-layer-group f-11 text-lightest"></i><span
                        class="ml-2 f-11 text-lightest projectNameSpan">{{ $service job->site->project_name }}</span>
                </div>
            @endif

            @if ($service job->estimate_hours > 0 || $service job->estimate_minutes > 0)
                <div  data-toggle="tooltip" data-original-title="@lang('app.quote'): {{ $service job->estimate_hours }} @lang('app.hrs') {{ $service job->estimate_minutes }} @lang('app.mins')">
                    <i class="fa fa-hourglass-half f-11 text-lightest"></i><span
                        class="ml-2 f-11 text-lightest">{{ $service job->estimate_hours }}:{{ $service job->estimate_minutes }}</span>
                </div>
            @endif
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-wrap">
                @foreach ($service job->users as $item)
                    <div class="avatar-img mr-1 rounded-circle">
                        <a href="{{ route('cleaners.show', $item->id) }}" alt="{{ $item->name }}"
                            data-toggle="tooltip" data-original-title="{{ $item->name }}"
                            data-placement="right"><img src="{{ $item->image_url }}"></a>
                    </div>
                @endforeach
            </div>
            @if ($service job->subtasks_count > 0)
                {{ $service job->completed_subtasks_count .'/' . $service job->subtasks_count }}
            @endif
            @if (!is_null($service job->due_date))
                @if ($service job->due_date->endOfDay()->isPast())
                    <div class="d-flex text-red">
                        <span class="f-12 ml-1"><i class="f-11 bi bi-calendar align-self-center"></i> {{ $service job->due_date->translatedFormat($company->date_format) }}</span>
                    </div>
                @elseif($service job->due_date->setTimezone($company->timezone)->isToday())
                    <div class="d-flex text-dark-green">
                        <i class="fa fa-calendar-alt f-11 align-self-center"></i><span class="f-12 ml-1">@lang('app.today')</span>
                    </div>
                @else
                    <div class="d-flex text-lightest">
                        <i class="fa fa-calendar-alt f-11 align-self-center"></i><span
                            class="f-12 ml-1">{{ $service job->due_date->translatedFormat($company->date_format) }}</span>
                    </div>
                @endif
            @endif

        </div>
    </div>
</div><!-- div end -->

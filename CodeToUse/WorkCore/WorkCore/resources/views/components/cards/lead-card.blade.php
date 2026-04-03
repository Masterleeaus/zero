@php
$moveClass = '';
@endphp
@if ($draggable == 'false')
    @php
        $moveClass = 'move-disable';
    @endphp
@endif

<div class="card rounded bg-white border-grey b-shadow-4 m-1 mb-2 {{ $moveClass }} service job-card" data-service job-id="{{ $enquiry->id }}"
    id="drag-service job-{{ $enquiry->id }}">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between mb-2">
            <a href="{{ route('deals.show', [$enquiry->id]) }}"
                class="f-12 f-w-500 text-dark mb-0 text-wrap openRightModal">{{ $enquiry->name }}
                @if (!is_null($enquiry->contact->client_id))
                <i class="fa fa-check-circle text-success" data-toggle="tooltip" data-original-title="@lang('modules.enquiry.convertedClient')"></i>
                @endif
            </a>
            @if (!is_null($enquiry->value))
                <div class="d-flex">
                    <span
                        class="ml-2 f-11 text-lightest">{{ currency_format($enquiry->value, $enquiry->currency_id) }}</span>
                </div>
            @endif
        </div>

        @if ($enquiry->contact->client_name)
            <div class="d-flex mb-3 align-items-center">
                <i class="fa fa-building f-11 text-lightest"></i><span
                    class="ml-2 f-11 text-lightest">{{ $enquiry->contact->client_name_salutation }}</span>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center">
            @if (!is_null($enquiry->agent_id))
                <div class="d-flex flex-wrap">
                    <div class="avatar-img mr-1 rounded-circle">
                        <a href="{{ route('cleaners.show', $enquiry->leadAgent->user_id) }}" alt="{{ $enquiry->leadAgent->user->name }}" data-toggle="tooltip"
                            data-original-title="{{ __('app.leadAgent') .' : '. $enquiry->leadAgent->user->name }}"
                            data-placement="right"><img src="{{ $enquiry->leadAgent->user->image_url }}"></a>
                    </div>
                </div>
            @endif
            @if ($enquiry->next_follow_up_date != null && $enquiry->next_follow_up_date != '')
                <div class="d-flex text-lightest">
                    <span class="f-12 ml-1"><i class="f-11 bi bi-calendar"></i> {{ \Carbon\Carbon::parse($enquiry->next_follow_up_date)->translatedFormat(company()->date_format) }}</span>
                </div>
            @endif

        </div>
    </div>
</div><!-- div end -->

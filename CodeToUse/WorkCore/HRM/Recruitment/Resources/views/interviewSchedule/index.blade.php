@extends('layouts.main')

@section('page-title')
    {{ __('Manage Interview Schedule') }}
@endsection

@section('page-breadcrumb')
    {{ __('Interview Schedule') }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('Modules/Hrm/Resources/assets/css/main.css')}}">
@endpush

@section('page-action')
<div>
    @permission('interview schedule create')
        <a  data-url="{{ route('interview-schedule.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Interview Schedule') }}" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection

@section('content')
<div class="row">

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Calendar') }}</h5>
            </div>
            <div class="card-body">
                <div id='calendar' class='calendar'></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">

        <div class="card">
            <div class="card-body">

                <h4 class="mb-4">{{ __('Schedule List') }}</h4>
                <ul class="event-cards list-group list-group-flush mt-3 w-100">
                    <li class="list-group-item card mb-3">
                    @forelse ($current_month_event as $schedule)
                        <div class="row align-items-center justify-content-between">
                           <div class=" align-items-center">
                             <div class="card mb-3 border shadow-none">
                                <div class="px-3">
                                    <div class="row align-items-center">
                                        <div class="col ml-n2 text-sm mb-0 fc-event-title-container">
                                                <h5 class="tcard-text small text-primary">{{ !empty($schedule->applications) ? (!empty($schedule->applications->jobs) ? $schedule->applications->jobs->title : '') : '' }}</h5>
                                                <div class="card-text small text-dark">{{ !empty($schedule->applications) ? $schedule->applications->name : '' }}
                                                </div>
                                                <div class="card-text small text-dark">{{company_date_formate($schedule->date) . ' ' . company_datetime_formate($schedule->time) }}
                                                </div>
                                        </div>
                                <div class="col-auto text-right">
                                    <div class="d-inline-flex mb-4">
                                        @permission('interview schedule edit')
                                                 <div class="action-btn bg-info ms-2">
                                                <a  class="mx-3 btn btn-sm  align-items-center"
                                                    data-url="{{ route('interview-schedule.edit', $schedule->id) }}"
                                                    data-ajax-popup="true" data-title="{{ __('Edit ') }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                        @endpermission
                                        @permission('interview schedule delete')
                                           <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['interview-schedule.destroy', $schedule->id], 'id' => 'delete-form-' . $schedule->id]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ __('Delete') }}">
                                                    <i class="ti ti-trash text-white"></i></a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endpermission
                                    </div>
                                </div>
                               </div>
                             </div>
                          </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center">
                        <h6>There is no Interview Schedule Create</h6>
                    </div>
                @endforelse
                 </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('Modules/Hrm/Resources/assets/js/main.min.js') }}"></script>
    <script type="text/javascript">
        (function() {
            var etitle;
            var etype;
            var etypeclass;
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    timeGridDay: "{{__('Day')}}",
                    timeGridWeek: "{{__('Week')}}",
                    dayGridMonth: "{{__('Month')}}"
                },
                themeSystem: 'bootstrap',
                slotDuration: '00:10:00',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                events: {!! $arrSchedule !!},
            });
            calendar.render();
        })();
    </script>



<script>
    $(document).on('click', '.fc-daygrid-event', function(e) {
        // if (!$(this).hasClass('project')) {
        e.preventDefault();
        var event = $(this);
        var title = $(this).find('.fc-event-title').html();
        var size = 'md';
        var url = $(this).attr('href');
        $("#commonModal .modal-title ").html(title);
        $("#commonModal .modal-dialog").addClass('modal-' + size);
        $.ajax({
            url: url,
            success: function(data) {
                $('#commonModal .body').html(data);
                $("#commonModal").modal('show');
                if ($(".d_week").length > 0) {
                    $($(".d_week")).each(function(index, element) {
                        var id = $(element).attr('id');

                        (function() {
                            const d_week = new Datepicker(document.querySelector('#' +
                                id), {
                                buttonClass: 'btn',
                                format: 'yyyy-mm-dd',
                            });
                        })();

                    });
                }

            },
            error: function(data) {
                data = data.responseJSON;
                toastrs('Error', data.error, 'error')
            }
        });
        // }
    });
</script>
@endpush

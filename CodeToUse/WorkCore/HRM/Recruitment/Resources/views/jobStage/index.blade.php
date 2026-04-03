@extends('layouts.main')

@section('page-title')
   {{ __('Manage Job Stage') }}
@endsection

@section('page-breadcrumb')
    {{ __('Job Stage') }}
@endsection

@section('page-action')
<div>
    @permission('jobstage create')
        <a  data-url="{{ route('job-stage.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Job Stage') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection


@section('content')
<div class="row">
    <div class="col-sm-3">
        @include('hrm::layouts.hrm_setup')
    </div>
            <div class="col-sm-9">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-11">
                                <h5 class="">
                                    {{ __('Job Stages') }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover " data-repeater-list="stages">
                            <thead>
                                <th><i class="fas fa-crosshairs"></i></th>
                                <th>{{ __('Name') }}</th>
                                <th class="d-flex justify-content-end">{{ __('Action') }}</th>
                            </thead>
                            <tbody class="task-stages">
                                @foreach ($stages as $stage)
                                <tr data-id="{{ $stage->id }}">
                                    <td><i class="fas fa-crosshairs sort-handler "></i></td>
                                    <td >{{ $stage->title }}</td>
                                    <td class="d-flex justify-content-end" >
                                    @permission('jobstage edit')
                                    <div class="action-btn bg-info ms-2">
                                        <a class="mx-3 btn btn-sm  align-items-center" 
                                            data-url="{{ route('job-stage.edit', $stage->id) }}" data-ajax-popup="true"
                                            data-size="md" data-bs-toggle="tooltip" title=""
                                            data-title="{{ __('Edit Job Stage') }}"
                                            data-bs-original-title="{{ __('Edit') }}"><i class="ti ti-pencil  text-white"></i></a>
                                    </div>
                                    @endpermission
                                    @permission('jobstage delete')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['job-stage.destroy', $stage->id], 'id' => 'delete-form-' . $stage->id]) !!}
                                            <a  class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                aria-label="Delete"><i class="ti ti-trash text-white "></i></a>
                                            </form>
                                        </div>
                                    @endpermission
                                </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                </div>

            </div>
            <div class="alert alert-dark" role="alert">
                {{__('Note : You can easily order change of job stage using drag & drop.')}}
            </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/jquery-ui.min.js') }}"></script>
    @if (\Auth::user()->type == 'company')
        <script>
           $(document).ready(function () {
            var $dragAndDrop = $("body .task-stages tbody").sortable({
                handle: '.sort-handler'
                });

                myFunction();
            });
            function myFunction(){
                $(".task-stages").sortable({
                    stop: function() {
                        var order = [];
                        $(this).find('tr').each(function(index, data) {
                            order[index] = $(data).attr('data-id');
                        });
                        $.ajax({
                            url: "{{ route('job.stage.order') }}",
                            data: {
                                order: order,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'POST',
                            success: function(data) {},
                            error: function(data) {
                                data = data.responseJSON;
                            }
                        })
                    }
                });
            }
        </script>
    @endif
@endpush

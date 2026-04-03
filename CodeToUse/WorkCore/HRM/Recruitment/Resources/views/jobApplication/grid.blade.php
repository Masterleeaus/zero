@extends('layouts.main')
@section('page-title')
    {{ __('Manage Job On-Boarding') }}
@endsection

@section('page-breadcrumb')
    {{ __('Job On-Boarding') }}
@endsection
@section('page-action')
    <div>
        <a href="{{ route('job.on.board') }}" class="btn btn-sm btn-primary btn-icon"
            data-bs-toggle="tooltip"title="{{ __('List View') }}">
            <i class="ti ti-list text-white"></i>
        </a>
        @permission('jobonboard create')
            <a  data-url="{{ route('job.on.board.create', 0) }}" data-ajax-popup="true"
                data-title="{{ __('Create New Job On-Boarding') }}" data-bs-toggle="tooltip" title=""
                class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/letter.avatar.js') }}"></script>
@endpush
@section('filter')
@endsection
@section('content')
    <div class="row">

        @foreach ($jobOnBoards as $job)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-center">
                            @if ($job->status == 'pending')
                                <span
                                    class="badge bg-warning p-2 px-3 rounded">{{ Modules\Recruitment\Entities\JobOnBoard::$status[$job->status] }}</span>
                            @elseif($job->status == 'cancel')
                                <span
                                    class="badge bg-danger p-2 px-3 rounded">{{ Modules\Recruitment\Entities\JobOnBoard::$status[$job->status] }}</span>
                            @else
                                <span
                                    class="badge bg-success p-2 px-3 rounded">{{ Modules\Recruitment\Entities\JobOnBoard::$status[$job->status] }}</span>
                            @endif
                        </div>
                        <div class="card-header-right">
                            <div class="btn-group card-option">
                                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="feather icon-more-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @if (Laratrust::hasPermission('jobonboard convert') || Laratrust::hasPermission('job edit') || Laratrust::hasPermission('job delete'))
                                        @permission('job edit')
                                            <a  data-url="{{ route('job.on.board.edit', $job->id) }}" data-ajax-popup="true" data-size="md"
                                                class="dropdown-item" data-bs-whatever="{{ __('Edit Job') }}"
                                                data-bs-toggle="tooltip" data-title="{{ __('Edit Job') }}"><i
                                                    class="ti ti-pencil"></i>
                                                {{ __('Edit') }}</a>
                                        @endpermission
                                        @permission('jobonboard convert')
                                            @if ($job->status == 'confirm' && $job->convert_to_employee == 0)
                                                <a href="{{ route('job.on.board.convert', $job->id) }}" class="dropdown-item">
                                                    <i class="ti ti-arrows-right-left "></i>{{ __(' Convert to Employee') }}
                                                </a>

                                            @elseif($job->status == 'confirm' && $job->convert_to_employee != 0)
                                                <a href="{{ route('employee.show', \Crypt::encrypt($job->convert_to_employee)) }}"
                                                    class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ __('Employee Detail') }}">{{ __('Employee Detail') }}
                                                    <i class="ti ti-eye "></i>
                                                </a>
                                            @endif
                                        @endpermission
                                        @if ($job->status == 'confirm' )
                                            <a href="{{ route('offerlatter.download.pdf',$job->id) }}" class="dropdown-item" target="_blanks">
                                                <i class="ti ti-file "></i>{{ __(' Offer Letter PDF') }}
                                            </a>
                                            <a href="{{ route('offerlatter.download.doc',$job->id) }}" class="dropdown-item" target="_blanks">
                                                <i class="ti ti-file "></i>{{ __(' Offer Letter DOC') }}
                                            </a>
                                        @endif
                                        @permission('job delete')
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['job.on.board.delete', $job->id]]) !!}
                                            <a href="#!" class="dropdown-item  show_confirm" data-bs-toggle="tooltip">
                                                <i class="ti ti-trash"></i>{{ __('Delete') }}
                                            </a>
                                            {!! Form::close() !!}
                                        @endpermission
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 justify-content-between">
                            <div class="col-12">
                                <div class="text-center client-box">
                                    <div class="avatar-parent-child mb-3">
                                        <img alt="user-image" class="img-fluid rounded-circle"
                                            avatar="{{ !empty($job->applications) ? $job->applications->name : '-' }}">
                                    </div>
                                    <div class="h6 mt-2 mb-1 ">
                                        <a class=" text-primary"
                                            >{{ !empty($job->applications) ? $job->applications->name : '-' }}</a>
                                    </div>
                                    <div class="mb-1"><a 
                                            class="text-sm small text-muted">{{ !empty($job->applications) ? (!empty($job->applications->jobs) ? (!empty($job->applications->jobs) ? (!empty($job->applications->jobs->branches) ? $job->applications->jobs->branches->name : '-') : '-') : '-') : '-' }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-md-3">
            <a  data-url="{{ route('job.on.board.create', 0) }}" class="btn-addnew-project" data-ajax-popup="true" data-title="{{ __('Create New Job On-Boarding') }}"style="padding: 62px 10px;">
                <div class="badge bg-primary proj-add-icon">
                    <i class="ti ti-plus"></i>
                </div>
                <h6 class="mt-4 mb-2">New Job On-Boarding</h6>
                <p class="text-muted text-center">Click here to add New Job On-Boarding</p>
            </a>
        </div>
    </div>
@endsection

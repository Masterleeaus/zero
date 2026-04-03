@extends('layouts.main')
@section('page-title')
    {{ __('Manage Job') }}
@endsection

@section('page-breadcrumb')
    {{ __('Job') }}
@endsection
@section('page-action')
    <div>
        <a href="{{ route('job.index') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
            title="{{ __('List View') }}">
            <i class="ti ti-list text-white"></i>
        </a>
        @permission('job create')
            <a href="{{ route('job.create') }}" data-size="md" data-title="{{ __('Create New Job') }}" data-bs-toggle="tooltip"
                title="" class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
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
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-primary">
                                    <i class="ti ti-briefcase"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{__('Total')}}</small>
                                    <h6 class="m-0">{{__('Jobs')}}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{$data['total']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-cast"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{__('Active')}}</small>
                                    <h6 class="m-0">{{__('Jobs')}}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{$data['active']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-warning">
                                    <i class="ti ti-cast"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{__('Inactive')}}</small>
                                    <h6 class="m-0">{{__('Jobs')}}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{$data['in_active']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @foreach ($jobs as $job)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-center">
                            @if ($job->status == 'active')
                                <span
                                    class="badge bg-success p-2 px-3 rounded status-badge">{{ Modules\Recruitment\Entities\Job::$status[$job->status] }}</span>
                            @else
                                <span
                                    class="badge bg-danger p-2 px-3 rounded status-badge">{{ Modules\Recruitment\Entities\Job::$status[$job->status] }}</span>
                            @endif
                        </div>
                        <div class="card-header-right">
                            <div class="btn-group card-option">
                                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="feather icon-more-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @if (Laratrust::hasPermission('job show') || Laratrust::hasPermission('job edit') || Laratrust::hasPermission('job delete'))
                                        @permission('job edit')
                                            <a href="{{ route('job.edit', $job->id) }}" data-size="md" class="dropdown-item"
                                                data-bs-whatever="{{ __('Edit Job') }}" data-bs-toggle="tooltip"
                                                data-title="{{ __('Edit Job') }}"><i class="ti ti-pencil"></i>
                                                {{ __('Edit') }}</a>
                                        @endpermission
                                        @permission('job show')
                                            <a href="{{ route('job.show', $job->id) }}" class="dropdown-item"
                                                data-bs-whatever="{{ __('Job Details') }}" data-bs-toggle="tooltip"
                                                data-title="{{ __('Job Details') }}"><i class="ti ti-eye"></i>
                                                {{ __('Details') }}</a>
                                        @endpermission

                                        @permission('job delete')
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['job.destroy', $job->id]]) !!}
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
                                            avatar="{{ $job->title }}">
                                    </div>
                                    <div class="h6 mt-2 mb-1 ">
                                        <a class=" text-primary"
                                            href="{{ route('job.show', $job->id) }}">{{ ucfirst($job->title) }}</a>
                                    </div>
                                    <div class="mb-1"><a 
                                            class="text-sm small text-muted">{{ !empty($job->branches) ? $job->branches->name : __('All') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-md-3">
            <a href="{{ route('job.create') }}" class="btn-addnew-project" data-title="{{ __('Create New job') }}" style="padding: 62px 10px;">
                <div class="badge bg-primary proj-add-icon">
                    <i class="ti ti-plus"></i>
                </div>
                <h6 class="mt-4 mb-2">New job</h6>
                <p class="text-muted text-center">Click here to add New job</p>
            </a>
        </div>
    </div>
@endsection

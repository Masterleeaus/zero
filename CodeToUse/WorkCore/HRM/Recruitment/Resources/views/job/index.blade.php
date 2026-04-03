@extends('layouts.main')
@section('page-title')
    {{ __('Manage Job') }}
@endsection

@section('page-breadcrumb')
    {{ __('Manage Job') }}
@endsection
@push('scripts')
    <script>
        $('.copy_link').click(function(e) {
            e.preventDefault();
            var copyText = $(this).attr('href');

            document.addEventListener('copy', function(e) {
                e.clipboardData.setData('text/plain', copyText);
                e.preventDefault();
            }, true);

            document.execCommand('copy');
            show_toastr('Success', 'Url copied to clipboard', 'success');
        });
        function copyToClipboard(element) {

            var copyText = element.id;
            document.addEventListener('copy', function (e) {
                e.clipboardData.setData('text/plain', copyText);
                e.preventDefault();
            }, true);

            document.execCommand('copy');
            toastrs('success', 'Url copied to clipboard', 'success');
        }
    </script>
@endpush
@section('page-action')
<div>
    <a href="{{ route('job.grid') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"title="{{ __('Grid View') }}">
        <i class="ti ti-layout-grid text-white"></i>
    </a>
    @permission('job create')
        <a href="{{ route('job.create') }}"  data-size="md" data-title="{{ __('Create New Job') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
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
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th>{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    @if (Laratrust::hasPermission('job edit') || Laratrust::hasPermission('job delete') || Laratrust::hasPermission('job show'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jobs as $job)
                                <tr>
                                    <td>{{ !empty($job->branches) ? $job->branches->name : __('All') }}</td>
                                    <td>{{ $job->title }}</td>
                                    <td>{{ company_date_formate($job->start_date) }}</td>
                                    <td>{{ company_date_formate($job->end_date) }}</td>
                                    <td>
                                        @if ($job->status == 'active')
                                            <span
                                                class="badge bg-success p-2 px-3 rounded status-badge">{{ Modules\Recruitment\Entities\Job::$status[$job->status] }}</span>
                                        @else
                                            <span
                                                class="badge bg-danger p-2 px-3 rounded status-badge">{{ Modules\Recruitment\Entities\Job::$status[$job->status] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ company_date_formate($job->created_at) }}</td>
                                    <td class="Action">
                                        @if (Laratrust::hasPermission('job edit') || Laratrust::hasPermission('job delete') || Laratrust::hasPermission('job show'))
                                        <span>
                                            @if($job->status!='in_active')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" id="{{ route('job.requirement',[$job->code,!empty($job)?$job->createdBy->lang:'en']) }}" class="mx-3 btn btn-sm align-items-center"  onclick="copyToClipboard(this)" data-bs-toggle="tooltip" title="{{__('Copy')}}" data-original-title="{{__('Click to copy')}}"><i class="ti ti-link text-white"></i></a>
                                                </div>
                                            @endif
                                            @permission('job show')
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('job.show', $job->id) }}"
                                                    class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ __('Job Detail') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            @endpermission

                                            @permission('job edit')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('job.edit', $job->id) }}" class="mx-3 btn btn-sm  align-items-center"
                                                        data-url=""
                                                        data-title="{{ __('Edit Job') }}"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endpermission

                                            @permission('job delete')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['job.destroy', $job->id], 'id' => 'delete-form-' . $job->id]) !!}
                                                    <a href="#!" class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                        title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash text-white"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endpermission
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

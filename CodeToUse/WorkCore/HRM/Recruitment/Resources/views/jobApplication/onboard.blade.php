@extends('layouts.main')
@section('page-title')
    {{ __('Manage Job On-Boarding') }}
@endsection
@section('page-action')
    <div>
        <a href="{{ route('job.on.board.grid') }}" class="btn btn-sm btn-primary btn-icon"
            data-bs-toggle="tooltip"title="{{ __('Grid View') }}">
            <i class="ti ti-layout-grid text-white"></i>
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
@section('page-breadcrumb')
    {{ __('Job On-Boarding') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Job') }}</th>
                                    <th>{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}
                                    </th>
                                    <th>{{ __('Applied at') }}</th>
                                    <th>{{ __('Joining at') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Laratrust::hasPermission('jobonboard convert') || Laratrust::hasPermission('jobonboard edit') || Laratrust::hasPermission('jobonboard delete'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jobOnBoards as $job)
                                    <tr>
                                        <td>{{ !empty($job->applications) ? $job->applications->name : '-' }}</td>
                                        <td>{{ !empty($job->applications) ? (!empty($job->applications->jobs) ? $job->applications->jobs->title : '-') : '-' }}
                                        </td>
                                        <td>{{ !empty($job->applications) ? (!empty($job->applications->jobs) ? (!empty($job->applications->jobs) ? (!empty($job->applications->jobs->branches) ? $job->applications->jobs->branches->name : '-') : '-') : '-') : '-' }}
                                        </td>
                                        <td>{{ company_date_formate(!empty($job->applications) ? $job->applications->created_at : '-') }}
                                        </td>
                                        <td>{{ company_date_formate($job->joining_date) }}</td>
                                        <td>
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
                                        </td>

                                        <td class="Action">
                                            <span>
                                                @permission('jobonboard convert')
                                                    @if ($job->status == 'confirm' && $job->convert_to_employee == 0 && PlanCheck('User',Auth::user()->id) == true)
                                                        <div class="action-btn bg-dark ms-2">
                                                            <a href="{{ route('job.on.board.converts', $job->id) }}"
                                                                class="mx-3 btn btn-sm  align-items-center"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Convert to Employee') }}">
                                                                <i class="ti ti-arrows-right-left text-white"></i>
                                                            </a>
                                                        </div>
                                                    @elseif($job->status == 'confirm' && $job->convert_to_employee != 0)
                                                        <div class="action-btn bg-warning ms-2">
                                                            <a href="{{ route('employee.show', \Crypt::encrypt($job->convert_to_employee)) }}"
                                                                class="mx-3 btn btn-sm  align-items-center"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Employee Detail') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endpermission
                                                @permission('jobonboard edit')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a  class="mx-3 btn btn-sm  align-items-center"
                                                            data-url="{{ route('job.on.board.edit', $job->id) }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Job On-Boarding') }}"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endpermission
                                                @permission('jobonboard delete')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['job.on.board.delete', $job->id],
                                                            'id' => 'delete-form-' . $job->id,
                                                        ]) !!}
                                                        <a href="#!"
                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ __('Delete') }}">
                                                            <i class="ti ti-trash text-white"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endpermission
                                                @if ($job->status == 'confirm' )
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{route('offerlatter.download.pdf',$job->id)}}" class="mx-3 btn btn-sm  align-items-center " data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('OfferLetter PDF')}}" target="_blanks"><i class="ti ti-download text-white"></i></a>
                                                </div>
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{route('offerlatter.download.doc',$job->id)}}" class="mx-3 btn btn-sm  align-items-center " data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('OfferLetter DOC')}}" target="_blanks"><i class="ti ti-download text-white"></i></a>
                                                </div>
                                            @endif
                                            </span>
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
@endsection

@extends('layouts.main')
@section('page-title')
    {{ __('Manage Archive Job Application') }}
@endsection

@section('page-breadcrumb')
    {{ __('Archive Job Application') }}
@endsection
@push('css')
    <link href="{{ asset('Modules/Recruitment/Resources/assets/css/bootstrap-tagsinput.css') }}" rel="stylesheet" />
    <link href="{{ asset('Modules/Recruitment/Resources/assets/css/custom.css') }}" rel="stylesheet" />

@endpush
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
                                <th>{{ __('Applied For') }}</th>
                                <th>{{ __('Rating') }}</th>
                                <th>{{ __('Applied at') }}</th>
                                <th>{{ __('Resume') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($archive_application as $application)
                                <tr>
                                    <td><a class="btn btn-outline-primary"
                                            href="{{ route('job-application.show', \Crypt::encrypt($application->id)) }}">
                                            {{ $application->name }}</a></td>
                                    <td>{{ !empty($application->jobs) ? $application->jobs->title : '-' }}</td>
                                    <td>

                                        <span class="static-rating static-rating-sm d-block">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $application->rating)
                                                    <i class="star fas fa-star voted"></i>
                                                @else
                                                    <i class="star fas fa-star"></i>
                                                @endif
                                            @endfor
                                        </span>

                                    </td>
                                    <td>{{ company_date_formate($application->created_at) }}</td>
                                    <td>
                                        @if (!empty($application->resume))
                                        <span class="text-sm action-btn bg-primary ms-2">
                                            <a class=" btn btn-sm align-items-center" href="{{ get_file($application->resume)  }}"
                                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('download') }}"
                                                download=""><i class="ti ti-download text-white"></i></a>
                                        </span>

                                        <div class="action-btn bg-secondary ms-2 ">
                                            <a class=" mx-3 btn btn-sm align-items-center" href="{{ get_file($application->resume) }}" target="_blank"  >
                                                <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>
                                            </a>
                                        </div>

                                    @else
                                        -
                                    @endif

                                    </td>
                                    <td>
                                        @permission('jobapplication show')
                                        <div class="action-btn bg-info ms-2">

                                            <a class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}" data-title="{{__('Details')}}" href="{{ route('job-application.show',\Crypt::encrypt($application->id)) }}"> <i class="ti ti-eye text-white"></i></a>
                                    </div>
                                        @endpermission
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

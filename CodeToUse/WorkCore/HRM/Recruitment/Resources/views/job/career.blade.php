@extends('recruitment::layouts.master')
@section('page-title')
    {{__('Career')}}
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
@endpush

@section('content')
<div class="job-wrapper">
    <div class="job-content">
        <nav class="navbar">
            <div class="container">
                <a class="navbar-brand" href="{{route('home')}}">
                    <img src="{{ !empty (get_file(company_setting('logo_light',$company_id,$workspace_id))) ? get_file('uploads/logo/logo_light.png') :'WorkDo'}}" alt="logo" style="width: 90px">
                </a>
                <li class="dropdown dash-h-item drp-language">
                    <div class="dropdown global-icon" data-toggle="tooltip" data-original-titla="{{ __('Choose Language') }}" >
                        <a class="nav-link px-0  btn bg-white px-3 py-2"  role="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" data-offset="0,10">
                            <span class="d-none d-lg-inline-block">{{ \Str::upper($currantLang) }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        <div class="dropdown-menu  dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="min-width: auto">
                            @foreach ($languages as $key=>$language)
                                <a class="dropdown-item @if ($key == $currantLang) text-danger @endif"
                                    href="{{ route('career', [$id, $key]) }}">{{$language}}</a>
                            @endforeach
                        </div>
                    </div>
                </li>
            </div>
        </nav>
        <section class="job-banner">
            <div class="job-banner-bg">
                <img src="{{ asset('Modules/Recruitment/Resources/assets/image/banner.png') }}" alt="">
            </div>
            <div class="container">
                <div class="job-banner-content text-center text-white">
                    <h1 class="text-white mb-3">
                        {{ __(' We help') }} <br> {{ __('businesses grow') }}
                    </h1>
                    <p>{{ __('Work there. Find the dream job you’ve always wanted..') }}</p>
                </div>
            </div>
        </section>
        <section class="placedjob-section">
            <div class="container">
                <div class="section-title bg-light">
                    @php
                        $totaljob = \Modules\Recruitment\Entities\Job::where('created_by', '=', $id)->where('workspace', '=', getActiveWorkSpace())->count();

                    @endphp

                    <h2 class="h1 mb-3"> <span class="text-primary">+{{ $totaljob }}
                        </span>{{ __('Job openings') }}</h2>
                    <p>{{ __('Always looking for better ways to do things, innovate') }} <br>
                        {{ __('and help people achieve their goals') }}.</p>
                </div>
                <div class="row g-4">
                    @foreach ($jobs as $job)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 job-card">
                            <div class="job-card-body">
                                <div class="d-flex mb-3 align-items-center justify-content-between ">
                                    <img src="{{ asset('Modules/Recruitment/Resources/assets/image/figma.png') }}" alt="">
                                    @if (!empty($job->branches) ? $job->branches->name : '')
                                        <span>{{ !empty($job->branches) ? $job->branches->name : '' }} <i
                                                class="ti ti-map-pin ms-1"></i></span>
                                    @endif
                                </div>
                                <h5 class="mb-3">
                                    <a href="{{ route('job.requirement', [$job->code, !empty($job) ? (!empty($currantLang) ? $currantLang : 'en') : 'en']) }}" target="_blank"
                                        class="text-dark">{{ $job->title }}
                                    </a>
                                </h5>
                                <div
                                    class="d-flex mb-3 align-items-start flex-column flex-xl-row flex-md-row flex-lg-column">
                                    <span class="d-inline-block me-2"> <i class="ti ti-circle-plus "></i>
                                        {{ $job->position }} {{ __('position available') }}</span>
                                </div>

                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    @foreach (explode(',', $job->skill) as $skill)
                                        <span class="badge rounded  p-2 bg-primary">{{ $skill }}</span>
                                    @endforeach

                                </div>

                                <a href="{{ route('job.requirement', [$job->code, !empty($job) ? (!empty($currantLang) ? $currantLang : 'en') : 'en']) }}" target="_blank"
                                    class="btn btn-primary w-100 mt-4">
                                    {{ __('Read more') }}
                                </a>

                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </section>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('Modules/Recruitment/Resources/assets/js/site.core.js') }}"></script>
<script src="{{ asset('Modules/Recruitment/Resources/assets/js/autosize.min.js') }}"></script>
<script src="{{ asset('Modules/Recruitment/Resources/assets/js/site.js') }}"></script>
<script src="{{ asset('Modules/Recruitment/Resources/assets/js/demo.js') }} "></script>
<link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
@endpush



@extends('recruitment::layouts.master')
@section('page-title')
{{$job->title}}
@endsection

@section('content')

<div class="job-wrapper">
    <div class="job-content">
        <nav class="navbar">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="{{ !empty (get_file(company_setting('logo_light',$job->created_by,$job->workspace))) ? get_file('uploads/logo/logo_light.png') :'WorkDo'}}" alt="logo" style="width: 90px">
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
                                <a class="dropdown-item @if ($key == $currantLang)  text-danger @endif"
                                    href="{{ route('job.apply', [$job->code, $key]) }}">{{$language}}</a>
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
                        {{__(' We help')}} <br> {{__('businesses grow')}}
                    </h1>
                    <p>{{ __('Work there. Find the dream job you’ve always wanted..') }}</p>
                    </p>
                </div>
            </div>
        </section>
        <section class="apply-job-section">
            <div class="container">
                <div class="apply-job-wrapper bg-light">
                    <div class="section-title text-center">
                        <h2 class="h1 mb-3"> {{$job->title}}</h2>
                        <div class="d-flex flex-wrap justify-content-center gap-1 mb-4">
                            @foreach (explode(',', $job->skill) as $skill)
                                <span class="badge rounded p-2 bg-primary">{{ $skill }}</span>
                            @endforeach
                        </div>
                        @if(!empty($job->branches)?$job->branches->name:'')
                            <p> <i class="ti ti-map-pin ms-1"></i> {{!empty($job->branches)?$job->branches->name:''}}</p>
                        @endif
                    </div>
                    <div class="apply-job-form">
                        <h2 class="mb-4">{{__('Apply for this job')}}</h2>
                        {{Form::open(array('route'=>array('job.apply.data',$job->code),'method'=>'post', 'enctype' => "multipart/form-data"))}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('name',__('Name'),['class'=>'form-label'])}}
                                    {{Form::text('name',null,array('class'=>'form-control name','required'=>'required'))}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                                    {{Form::text('email',null,array('class'=>'form-control','required'=>'required'))}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('phone',__('Phone'),['class'=>'form-label'])}}
                                    {{Form::text('phone',null,array('class'=>'form-control','required'=>'required'))}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if(!empty($job->applicant) && in_array('dob',explode(',',$job->applicant)))
                                    <div class="form-group">
                                        {!! Form::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}
                                        {!! Form::date('dob', old('dob'), ['class' => 'form-control datepicker w-100','required'=>'required']) !!}
                                    </div>
                                @endif
                            </div>
                            @if(!empty($job->applicant) && in_array('gender',explode(',',$job->applicant)))
                                <div class="form-group col-md-6 ">
                                    {!! Form::label('gender', __('Gender'),['class'=>'form-label']) !!}
                                    <div class="d-flex radio-check">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="g_male" value="Male" name="gender" class="custom-control-input" >
                                            <label class="custom-control-label" for="g_male">{{__('Male')}}</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="g_female" value="Female" name="gender" class="custom-control-input">
                                            <label class="custom-control-label" for="g_female">{{__('Female')}}</label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($job->applicant) && in_array('country',explode(',',$job->applicant)))
                                <div class="form-group col-md-6 ">
                                    {{Form::label('country',__('Country'),['class'=>'form-label'])}}
                                    {{Form::text('country',null,array('class'=>'form-control','required'=>'required'))}}
                                </div>
                                <div class="form-group col-md-6 country">
                                    {{Form::label('state',__('State'),['class'=>'form-label'])}}
                                    {{Form::text('state',null,array('class'=>'form-control','required'=>'required'))}}
                                </div>
                                <div class="form-group col-md-6 country">
                                    {{Form::label('city',__('City'),['class'=>'form-label'])}}
                                    {{Form::text('city',null,array('class'=>'form-control','required'=>'required'))}}
                                </div>
                            @endif

                            @if(!empty($job->visibility) && in_array('profile',explode(',',$job->visibility)))
                                <div class="form-group col-md-6 ">
                                    {{Form::label('profile',__('Profile'),['class'=>'col-form-label'])}}
                                    <input type="file" class="form-control" name="profile" id="profile" data-filename="profile_create" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                    <img id="blah" src="" class="mt-3" width="25%"/>
                                    <p class="profile_create"></p>
                                </div>
                            @endif

                            @if(!empty($job->visibility) && in_array('resume',explode(',',$job->visibility)))
                                <div class="form-group col-md-6 ">
                                    {{Form::label('resume',__('CV / Resume'),['class'=>'col-form-label'])}}
                                    <input type="file" class="form-control" name="resume" id="resume" data-filename="resume_create" onchange="document.getElementById('blah1').src = window.URL.createObjectURL(this.files[0])" required>
                                    <img id="blah1" class="mt-3" src="" width="25%"/>
                                    <p class="resume_create"></p>
                                </div>
                            @endif

                            @if(!empty($job->visibility) && in_array('letter',explode(',',$job->visibility)))
                                <div class="form-group col-md-12 ">
                                    {{Form::label('cover_letter',__('Cover Letter'),['class'=>'form-label'])}}
                                    {{Form::textarea('cover_letter',null,array('class'=>'form-control','rows'=>'3'))}}
                                </div>
                            @endif

                            @foreach($questions as $question)
                                <div class="form-group col-md-12  question question_{{$question->id}}">
                                    {{Form::label($question->question,$question->question,['class'=>'form-label'])}}
                                    <input type="text" class="form-control" name="question[{{$question->question}}]" {{($question->is_required=='yes')?'required':''}}>
                                </div>
                            @endforeach
                            <div class="col-12">
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary">{{__('Submit your application')}}</button>
                                </div>
                            </div>
                        </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/site.core.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/autosize.min.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/site.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/demo.js') }} "></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/daterangepicker.js') }}"></script>
@endpush

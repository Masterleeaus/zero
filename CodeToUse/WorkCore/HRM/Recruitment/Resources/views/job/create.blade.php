@extends('layouts.main')
@section('page-title')
    {{ __('Create Job') }}
@endsection
@push('css')
<link href="{{ asset('Modules/Recruitment/Resources/assets/css/bootstrap-tagsinput.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('Modules/Recruitment/Resources/assets/css/custom.css') }}">
<link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush
@section('page-action')
    <div class="">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn', [
                'template_module' => 'job',
                'module' => 'Recruitment',
            ])
        @endif
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/editorplaceholder.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/bootstrap-tagsinput.min.js') }}"></script>
    <script>
        var e = $('[data-toggle="tags"]');
        e.length && e.each(function() {
            $(this).tagsinput({
                tagClass: "badge badge-primary",
            })
        });


        $("#submit").click(function() {
            var skill = $('.skill_data').val();

            if (skill == '') {
                $('#skill_validation').removeClass('d-none')
                return false;
            } else {
                $('#skill_validation').addClass('d-none')
            }

            var description = CKEDITOR.instances['description'].getData();
            if (!isNaN(description)) {
                $('#description_val').removeClass('d-none')
                return false;
            } else {
                $('#description_val').addClass('d-none')
            }

            var requirement = CKEDITOR.instances['requirement'].getData();
            if (!isNaN(requirement)) {
                $('#req_val').removeClass('d-none')
                return false;
            } else {
                $('#req_val').addClass('d-none')
            }
        });
    </script>
@endpush

@section('page-breadcrumb')
    {{ __('Manage Job') }},
    {{ __('Create Job') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
    <div class="row">
        {{ Form::open(['url' => 'job', 'method' => 'post']) }}

        <div class="row mt-3">
            <div class="col-md-6 ">
                <div class="card card-fluid jobs-card">
                    <div class="card-body ">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::label('title', __('Job Title'), ['class' => 'col-form-label']) !!}
                                {!! Form::text('title', old('title'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'Enter job title',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label(
                                    'branch',
                                    !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'),
                                    ['class' => 'col-form-label'],
                                ) !!}
                                {{ Form::select('branch', $branches, null, ['class' => 'form-control ', 'placeholder' => 'Select Branch', 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('category', __('Job Category'), ['class' => 'col-form-label']) !!}
                                {{ Form::select('category', $categories, null, ['class' => 'form-control ', 'placeholder' => 'Select Job Category', 'required' => 'required']) }}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('position', __('No. of Positions'), ['class' => 'col-form-label']) !!}
                                {!! Form::number('position', old('positions'), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'step' => '1',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('status', __('Status'), ['class' => 'col-form-label']) !!}
                                {{ Form::select('status', $status, null, ['class' => 'form-control ', 'placeholder' => 'Select Status', 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) !!}
                                {!! Form::date('start_date', old('start_date'), [
                                    'class' => 'form-control ',
                                    'autocomplete' => 'off',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) !!}
                                {!! Form::date('end_date', old('end_date'), [
                                    'class' => 'form-control ',
                                    'autocomplete' => 'off',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="skill">{{ __('Skill Box') }}</label>
                                <input type="text" class="form-control skill_data" value="" data-toggle="tags"
                                    name="skill" placeholder="Skill" />

                            </div>
                            <p class="text-danger d-none" id="skill_validation">{{ __('Skill filed is required.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 ">
                <div class="card card-fluid jobs-card">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <h6>{{ __('Need to Ask ?') }}</h6>
                                    <div class="my-4">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]"
                                                value="gender" id="check-gender">
                                            <label class="form-check-label" for="check-gender">{{ __('Gender') }} </label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]"
                                                value="dob" id="check-dob">
                                            <label class="form-check-label"
                                                for="check-dob">{{ __('Date Of Birth') }}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]"
                                                value="country" id="check-country">
                                            <label class="form-check-label" for="check-country">{{ __('Country') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <h6>{{ __('Need to show Option ?') }}</h6>
                                    <div class="my-4">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="visibility[]"
                                                value="profile" id="check-profile">
                                            <label class="form-check-label" for="check-profile">{{ __('Profile Image') }}
                                            </label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="visibility[]"
                                                value="resume" id="check-resume">
                                            <label class="form-check-label" for="check-resume">{{ __('Resume') }}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="visibility[]"
                                                value="letter" id="check-letter">
                                            <label class="form-check-label"
                                                for="check-letter">{{ __('Cover Letter') }}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="visibility[]"
                                                value="terms" id="check-terms">
                                            <label class="form-check-label"
                                                for="check-terms">{{ __('Terms And Conditions') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <h6>{{ __('Custom Questions') }}</h6>
                                <div class="my-4">
                                    @foreach ($customQuestion as $question)
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="custom_question[]"
                                                value="{{ $question->id }}"
                                                @if ($question->is_required == 'yes') required @endif
                                                id="custom_question_{{ $question->id }}">
                                            <label class="form-check-label"
                                                for="custom_question_{{ $question->id }}">{{ $question->question }}
                                                @if ($question->is_required == 'yes')
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-fluid jobs-card">
                    <div class="card-body ">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::label('description', __('Job Description'), ['class' => 'col-form-label']) !!}
                                <textarea name="description"
                                class="form-control dec_data summernote {{ !empty($errors->first('description')) ? 'is-invalid' : '' }}" required
                                id="description"></textarea>

                                <p class="text-danger d-none" id="description_val">{{ __('This filed is required.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-fluid jobs-card">
                    <div class="card-body ">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::label('requirement', __('Job Requirement'), ['class' => 'col-form-label']) !!}
                                <textarea name="requirement"
                                class="form-control req_data summernote  {{ !empty($errors->first('requirement')) ? 'is-invalid' : '' }}" required
                                id="requirement"></textarea>
                                <p class="text-danger d-none" id="req_val">{{ __('This filed is required.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 text-end">
                <div class="form-group">
                    <input type="submit" id="submit" value="{{ __('Create') }}" class="btn btn-primary">
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection

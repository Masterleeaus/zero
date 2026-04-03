@extends('layouts.main')
@section('page-title')
    {{ __('Convert To Employee') }}
@endsection


@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/Recruitment/Resources/assets/css/custom.css') }}">
@endpush
@section('page-breadcrumb')
    {{ __('Job OnBoard') }},
    {{ __('Convert To Employee') }}
@endsection
@section('page-action')
    <div>
        <a href="{{ route('job.on.board') }}" class="btn-submit btn btn-sm btn-primary " data-toggle="tooltip"
            title="{{ __('Back') }}">
            <i class=" ti ti-arrow-back-up"></i> </a>
    </div>
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
    <div class="row">
        <div class="row">
            {{ Form::open(['route' => ['job.on.board.convert', $jobOnBoard->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
        </div>
        <div class="col-md-6 ">
            <div class="card  emp-card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Personal Detail') }}</h6>
                </div>
                <div class="card-body ">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('name', __('Name'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::text('name', !empty($jobOnBoard->applications) ? $jobOnBoard->applications->name : '', [
                                'class' => 'form-control',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('phone', __('Phone'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::text('phone', !empty($jobOnBoard->applications) ? $jobOnBoard->applications->phone : '', [
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('dob', __('Date of Birth'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::date('dob', !empty($jobOnBoard->applications) ? $jobOnBoard->applications->dob : '', [
                                    'class' => 'form-control  ',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="form-group ">
                                {!! Form::label('gender', __('Gender'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                                <div class="d-flex radio-check">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="g_male" value="Male" name="gender"
                                            class="form-check-input"
                                            {{ !empty($jobOnBoard->applications) && $jobOnBoard->applications->gender == 'Male' ? 'checked' : '' }}>
                                        <label class="form-check-label " for="g_male">{{ __('Male') }}</label>
                                    </div>
                                    <div class="custom-control custom-radio ms-1 custom-control-inline">
                                        <input type="radio" id="g_female" value="Female" name="gender"
                                            class="form-check-input"
                                            {{ !empty($jobOnBoard->applications) && $jobOnBoard->applications->gender == 'Female' ? 'checked' : '' }}>
                                        <label class="form-check-label " for="g_female">{{ __('Female') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('email', __('Email'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::email('email', old('email'), ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('password', __('Password'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::password('password', ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('address', __('Address'), ['class' => 'col-form-label']) !!}<span class="text-danger pl-1">*</span>
                        {!! Form::textarea('address', old('address'), [
                            'class' => 'form-control',
                            'rows' => 2,
                            'placeholder' => 'Enter Address',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 ">
            <div class="card emp-card  ">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Company Detail') }}</h6>
                </div>
                <div class="card-body employee-detail-create-body">
                    <div class="row">
                        @csrf
                        <div class="form-group col-md-12">
                            {!! Form::label('employee_id', __('Employee ID'), ['class' => 'col-form-label']) !!}
                            {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('branch_id', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}<span
                                class="text-danger pl-1">*</span>
                            {{ Form::select('branch_id', $branches, null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Branch']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('department_id', !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'), ['class' => 'form-label']) }}<span
                                class="text-danger pl-1">*</span>
                            {{ Form::select('department_id', [], null, ['class' => 'form-control ', 'id' => 'department_id', 'required' => 'required', 'placeholder' => 'Select Department']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('designation_id', !empty($company_settings['hrm_designation_name']) ? $company_settings['hrm_designation_name'] : __('Designation'), ['class' => 'form-label']) }}<span
                                class="text-danger pl-1">*</span>
                            {{ Form::select('designation_id', [], null, ['class' => 'form-control ', 'id' => 'designation_id', 'required' => 'required', 'placeholder' => 'Select Designation']) }}
                        </div>
                        <div class="form-group col-md-6">
                            <div>
                                {{ Form::label('roles', __('Roles'), ['class' => 'form-label']) }}
                                {{ Form::select('roles', $roles, null, ['class' => 'form-control ', 'id' => 'user_id', 'data-toggle' => 'select']) }}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('company_doj', __('Company Date Of Joining'), ['class' => 'col-form-label']) !!}
                            {!! Form::date('company_doj', $jobOnBoard->joining_date, ['class' => 'form-control ', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 ">
            <div class="card bank-card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Document') }}</h6>
                </div>
                <div class="card-body employee-detail-create-body">
                    @foreach ($documents as $key => $document)
                        <div class="row">
                            <div class="form-group col-12 d-flex">
                                <div class="float-left col-4">
                                    <label for="document" class=" form-label">{{ $document->name }}
                                        @if ($document->is_required == 1)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                </div>
                                <div class="float-right col-8">
                                    <input type="hidden" name="emp_doc_id[{{ $document->id }}]" id=""
                                        value="{{ $document->id }}">
                                    @php
                                        $employeedoc = !empty($employee->documents) ? $employee->documents()->pluck('document_value', __('document_id')) : [];
                                    @endphp
                                    <div class="choose-files">
                                        <label for="document[{{ $document->id }}]">
                                            <div class=" bg-primary "> <i
                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                            <input type="file" class="form-control file" data-filename="documents"
                                                @error('document') is-invalid @enderror
                                                @if ($document->is_required == 1) required @endif
                                                name="document[{{ $document->id }}]" id="document[{{ $document->id }}]"
                                                data-filename="{{ $document->id . '_filename' }}"
                                                onchange="document.getElementById('{{ 'blah' . $key }}').src = window.URL.createObjectURL(this.files[0])">
                                        </label>
                                        <img class=" mx-3" id="{{ 'blah' . $key }}"
                                            src="{{ isset($employeedoc[$document->id]) && !empty($employeedoc[$document->id]) ? get_file($employeedoc[$document->id]) : '' }}"
                                            style="width: 25%" />
                                    </div>
                                    @if (!empty($employeedoc[$document->id]))
                                        <span class="text-xs-1"><a
                                                href="{{ !empty($employeedoc[$document->id]) ? get_file($employeedoc[$document->id]) : '' }}"
                                                target="_blank"></a>
                                        </span>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6 ">
            <div class="card bank-card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Bank Account Detail') }}</h6>
                </div>
                <div class="card-body employee-detail-create-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'col-form-label']) !!}
                            {!! Form::text('account_holder_name', old('account_holder_name'), [
                                'class' => 'form-control',
                                'placeholder' => 'Enter Account Holder Name',
                            ]) !!}

                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('account_number', __('Account Number'), ['class' => 'col-form-label']) !!}
                            {!! Form::number('account_number', old('account_number'), [
                                'class' => 'form-control',
                                'placeholder' => 'Enter Account Number',
                            ]) !!}

                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('bank_name', __('Bank Name'), ['class' => 'col-form-label']) !!}
                            {!! Form::text('bank_name', old('bank_name'), ['class' => 'form-control', 'placeholder' => 'Enter Bank Name']) !!}

                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => 'col-form-label']) !!}
                            {!! Form::text('bank_identifier_code', old('bank_identifier_code'), [
                                'class' => 'form-control',
                                'placeholder' => 'Enter Bank Identifier Code',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('branch_location', __('Branch Location'), ['class' => 'col-form-label']) !!}
                            {!! Form::text('branch_location', old('branch_location'), [
                                'class' => 'form-control',
                                'placeholder' => 'Enter Branch Location',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'col-form-label']) !!}
                            {!! Form::text('tax_payer_id', old('tax_payer_id'), [
                                'class' => 'form-control',
                                'placeholder' => 'Enter Tax Payer Id',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($user) == null)
            <div class="col-12">
                {!! Form::submit('Create', ['class' => 'btn  btn-primary float-end']) !!}
                {{ Form::close() }}
            </div>
    </div>
    @endif
@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).on('change', '#branch_id', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id) {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }

            $.ajax({
                url: '{{ route('employee.getdepartment') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department_id').empty();
                    $('#department_id').append(
                        '<option value="" disabled>{{ __('Select Department') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department_id').append('<option value="' + key + '">' + value +
                        '</option>');
                    });
                    $('#department_id').val('');
                }
            });
        }

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {
            $.ajax({
                url: '{{ route('employee.getdesignation') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#designation_id').empty();
                    $('#designation_id').append(
                        '<option value="">{{ __('Select Designation') }}</option>');
                    $.each(data, function(key, value) {
                        $('#designation_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        }
    </script>
@endpush

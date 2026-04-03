@php

    $dark_logo=get_file(sidebar_logo());
    $company_settings = getCompanyAllSetting($company_id,$workspace_id);
    $admin_settings = getAdminAllSetting();
    $favicon = isset($company_settings['favicon']) ? $company_settings['favicon'] : (isset($admin_settings['favicon']) ? $admin_settings['favicon'] : 'uploads/logo/favicon.png');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <title>{{(company_setting('header_text',$company_id,$workspace_id)) ? company_setting('header_text',$company_id,$workspace_id) : config('app.name', 'LeadGo')}} &dash; {{__('Form')}}</title>
    <!-- HTML5 Shim and Respond.js IE11 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 11]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui"
    />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Dashboard Template Description" />
    <meta name="keywords" content="Dashboard Template" />
    <meta name="author" content="Rajodiya Infotech" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon icon -->
    <link rel="icon" href="{{(!empty($favicon) ? get_file($favicon) : get_file('uploads/logo/favicon.png'))}}" type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{asset('assets/fonts/tabler-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/feather.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/fontawesome.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/material.css')}}">

    <!-- vendor css -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}" id="main-style-link">
    <link rel="stylesheet" href="{{asset('assets/css/customizer.css')}}">
  </head>

  <body class="{{ !empty(company_setting('color',$company_id,$workspace_id))?company_setting('color',$company_id,$workspace_id):'theme-1' }}">
    <!-- [ auth-signup ] start -->
    <div class="auth-wrapper auth-v1">
      <div class="auth-content">

        <div class="row align-items-center justify-content-center text-start">
          <div class="col-xl-6 text-center">
            <div class="mx-3 mx-md-5">
              <h2 class="mb-3 text-white f-w-600"><img src="{{( !empty($dark_logo) ? $dark_logo : 'logo-dark.png')}}" alt="{{ config('app.name', 'Workdo Crm') }}" class="navbar-brand-img" style="max-width: 250px;"></h2>
            </div>
            <div class="card">
              <div class="card-body mx-auto">
                 @if($form->is_active == 1)
                    <div class="page-title"><h5>{{$form->name}}</h5></div>
                    <form method="POST" action="{{ route('form.view.store') }}">
                        @csrf
                        @if($objFields && $objFields->count() > 0)
                            @foreach($objFields as $objField)
                                @if($objField->type == 'text')
                                    <div class="form-group">
                                        {{ Form::label('field-'.$objField->id, __($objField->name),['class'=>'col-form-label']) }}
                                        {{ Form::text('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                    </div>
                                @elseif($objField->type == 'email')
                                    <div class="form-group">
                                        {{ Form::label('field-'.$objField->id, __($objField->name),['class'=>'col-form-label']) }}
                                        {{ Form::email('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                    </div>
                                @elseif($objField->type == 'number')
                                    <div class="form-group">
                                        {{ Form::label('field-'.$objField->id, __($objField->name),['class'=>'col-form-label']) }}
                                        {{ Form::number('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                    </div>
                                @elseif($objField->type == 'date')
                                    <div class="form-group">
                                        {{ Form::label('field-'.$objField->id, __($objField->name),['class'=>'col-form-label']) }}
                                        {{ Form::date('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                    </div>
                                @elseif($objField->type == 'textarea')
                                    <div class="form-group">
                                        {{ Form::label('field-'.$objField->id, __($objField->name),['class'=>'col-form-label']) }}
                                        {{ Form::textarea('field['.$objField->id.']', null, array('class' => 'form-control','required'=>'required','id'=>'field-'.$objField->id)) }}
                                    </div>
                                @endif
                            @endforeach
                            <input type="hidden" value="{{$code}}" name="code">
                            <div class="d-grid">
                                <button class="btn btn-primary btn-block mt-2">{{__('Submit')}}</button>
                              </div>
                        @endif
                    </form>
                @else
                    <div class="page-title"><h5>{{__('Form is not active.')}}</h5></div>
                @endif

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- [ auth-signup ] end -->

    <!-- Required Js -->
    <script src="{{asset('assets/js/vendor-all.js')}}"></script>
    <script src="{{asset('assets/js/plugins/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/feather.min.js')}}"></script>
    <script>
      feather.replace();
    </script>
     @if($admin_settings['enable_cookie'] == 'on')
        @include('layouts.cookie_consent')
    @endif
    <script src="{{ asset('js/custom.js') }}"></script>
    @if ($message = Session::get('success'))
        <script>
            toastrs('Success', '{!! $message !!}', 'success');
        </script>
    @endif
    @if ($message = Session::get('error'))
        <script>
            toastrs('Error', '{!! $message !!}', 'error');
        </script>
    @endif
  </body>
</html>




<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Template CSS -->
    <!-- <link type="text/css" rel="stylesheet" media="all" href="css/main.css"> -->

    <title>@lang('modules.service agreements.contractNumber') - #{{ $service agreement->contract_number }}</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $service agreement->company->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    <style>

@font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew_Bold.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew_Bold_Italic.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew_Italic.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'BeVietnamPro';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/BeVietnamPro-Black.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'BeVietnamPro';
            font-style: italic;
            font-weight: normal;
            src: url("{{ storage_path('fonts/BeVietnamPro-BlackItalic.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'BeVietnamPro';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/BeVietnamPro-bold.ttf') }}") format('truetype');
        }

        @if($invoiceSetting->is_chinese_lang)
            @font-face {
            font-family: SimHei;
            /*font-style: normal;*/
            font-weight: bold;
            src: url('{{ asset('fonts/simhei.ttf') }}') format('truetype');
        }

        @endif

    @php
        $font = '';
        if($invoiceSetting->locale == 'ja') {
            $font = 'ipag';
        } else if($invoiceSetting->locale == 'hi') {
            $font = 'hindi';
        } else if($invoiceSetting->locale == 'th') {
            $font = 'THSarabunNew';
        } else if($invoiceSetting->is_chinese_lang) {
            $font = 'SimHei';
        } else if($invoiceSetting->locale == 'vi') {
            $font = 'BeVietnamPro';
        }else {
            $font = 'Verdana';
        }
    @endphp

    @if($invoiceSetting->is_chinese_lang)
        body {
            font-weight: normal !important;
        }
    @endif

       body {
            margin: 0;
            font-family: {{$font}}, DejaVu Sans, sans-serif;
        }

        .bg-grey {
            background-color: #F2F4F7;
        }

        .bg-white {
            background-color: #fff;
        }

        .border-radius-25 {
            border-radius: 0.25rem;
        }

        .p-25 {
            padding: 1.25rem;
        }

        .f-13 {
            font-size: 13px;
        }

        .f-14 {
            font-size: 14px;
        }

        .f-15 {
            font-size: 15px;
        }

        .f-21 {
            font-size: 21px;
        }

        .text-black {
            color: #28313c;
        }

        .text-grey {
            color: #616e80;
        }

        .font-weight-700 {
            font-weight: 700;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        . {
            text-transform: capitalize;
        }

        .line-height {
            line-height: 24px;
        }

        .mt-1 {
            margin-top: 1rem;
        }

        .mb-0 {
            margin-bottom: 0px;
        }

        .b-collapse {
            border-collapse: collapse;
        }

        .heading-table-left {
            padding: 6px;
            border: 1px solid #DBDBDB;
            font-weight: bold;
            background-color: #f1f1f3;
            border-right: 0;
        }

        .heading-table-right {
            padding: 6px;
            border: 1px solid #DBDBDB;
            border-left: 0;
        }

        .unpaid {
            color: #000000;
            position: relative;
            padding: 11px 22px;
            font-size: 15px;
            border-radius: 0.25rem;
            width: 100px;
            text-align: center;
        }

        .main-table-heading {
            border: 1px solid #DBDBDB;
            background-color: #f1f1f3;
            font-weight: 700;
        }

        .main-table-heading td {
            padding: 11px 10px;
            border: 1px solid #DBDBDB;
        }

        .main-table-items td {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
        }

        .total-box {
            border: 1px solid #e7e9eb;
            padding: 0px;
            border-bottom: 0px;
        }

        .subtotal {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
        }

        .subtotal-amt {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-right: 0;
        }

        .total {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            font-weight: 700;
            border-left: 0;
        }

        .total-amt {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-right: 0;
            font-weight: 700;
        }

        .balance {
            font-size: 16px;
            font-weight: bold;
            background-color: #f1f1f3;
        }

        .balance-left {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
        }

        .balance-right {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-right: 0;
        }

        .centered {
            margin: 0 auto;
        }

        .rightaligned {
            margin-right: 0;
            margin-left: auto;
        }

        .leftaligned {
            margin-left: 0;
            margin-right: auto;
        }

        .page_break {
            page-break-before: always;
        }

        .logo {
            height: 50px;
        }

        @if($invoiceSetting->locale == 'th')

            table td {
            font-weight: bold !important;
            font-size: 20px !important;
            }

            .description {
            font-weight: bold !important;
            font-size: 16px !important;
            }

       @endif
    </style>
</head>

<body class="content-wrapper">
    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tbody>
            <!-- Table Row Start -->
            <tr>
                <td><img src="{{ $service agreement->company->invoiceSetting->logo_url }}" alt="{{ $service agreement->company->company_name }}"
                        class="logo" /></td>
                <td align="right" class="f-21 text-black font-weight-700 text-uppercase">@lang('app.menu.service agreement')</td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td>
                    <p class="line-height mt-1 mb-0 f-14 text-black">
                        {{ $service agreement->company->company_name }}<br>
                        @if (!is_null($service agreement->company))
                            {!! nl2br($service agreement->company->defaultAddress->address) !!}<br>
                            {{ $service agreement->company->company_phone }}
                        @endif

                    </p>
                </td>
                <td>
                    <table class="text-black mt-1 f-13 b-collapse rightaligned">
                        <tr>
                            <td class="heading-table-left">@lang('modules.service agreements.contractNumber')</td>
                            <td class="heading-table-right">#{{ $service agreement->contract_number }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('modules.sites.startDate')</td>
                            <td class="heading-table-right">{{ $service agreement->start_date->translatedFormat($service agreement->company->date_format) }}
                            </td>
                        </tr>
                        @if ($service agreement->end_date != null)
                            <tr>
                                <td class="heading-table-left">@lang('modules.service agreements.endDate')</td>
                                <td class="heading-table-right">
                                    {{ $service agreement->end_date->translatedFormat($service agreement->company->date_format) }}
                                </td>
                            </tr>
                        @endif
                        <tr class="description">
                            <td class="heading-table-left description">@lang('modules.service agreements.contractType')</td>
                            <td class="heading-table-right description">{{ $service agreement->contractType->name }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td height="30"></td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td colspan="2">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="f-14 text-black">

                                <p class="line-height mb-0">
                                    <span class="text-grey ">@lang('app.customer')</span><br>
                                    {{ $service agreement->customer->name_salutation }}<br>
                                    {{ $service agreement->customer->clientDetails->company_name }}
                                    {!! nl2br($service agreement->customer->clientDetails->address) !!}
                                </p>

                            </td>

                            <td align="right">
                                @if ($service agreement->customer->clientDetails->company_logo)
                                    <div class="text-uppercase bg-white unpaid rightaligned">
                                        <img src="{{ $service agreement->customer->clientDetails->image_url }}"
                                            alt="{{ $service agreement->customer->clientDetails->company_name }}"
                                            class="logo" />
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>


            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td height="20" colspan="2"></td>
            </tr>
            <!-- Table Row End -->

        </tbody>
    </table>

    <div>
        <h5 class="text-grey ">@lang('app.subject')</h5>
        <p class="f-15 text-black">{{ $service agreement->subject }}</p>

        <h5 class="text-grey ">@lang('modules.service agreements.notes')</h5>
        <p class="f-15 text-black">{{ $service agreement->contract_note }}</p>

        <h5 class="text-grey ">@lang('app.description')</h5>
        <p class="f-15 text-black">{!! nl2br(pdfStripTags($service agreement->contract_detail)) !!}</p>

        @if ($service agreement->amount != 0)
            <div class="text-right pt-3 border-top description" align="right">
                <h4>@lang('modules.service agreements.contractValue'):
                    {{ $service agreement->amount . ' ' . $service agreement->currency->currency_code }}</h4>
            </div>
        @endif

        <hr class="mt-1 mb-1">
        @if ($service agreement->company_sign)
            <div style="text-align: left; margin-top: 10px">
                <h4 class="name" style="margin-bottom: 20px;">@lang('modules.quotes.companysignature')</h4>
                <img src="{{ $service agreement->company_signature }}" style="width: 200px;">
                <p>Date:- {{ $service agreement->sign_date->timezone($company->timezone) }}</p>
                @if($service agreement->signer)
                <p style="margin-top: -16px;">Sign By : {{ $service agreement->signer ? $service agreement->signer->name : '--' }}</p>
                @endif
            </div>
        @endif

        @if ($service agreement->signature)
            <div style="text-align: right; margin-top: -260px">
                <h4 class="name" style="margin-bottom: 10px;">@lang('modules.quotes.clientsignature')</h4>
                {!! Html::image($service agreement->signature->signature, '', ['class' => '', 'height' => '75px']) !!}
                <p>Customer Name:- {{ $service agreement->signature->full_name }}<br>
                    Place:- {{ $service agreement->signature->place }}<br>
                    Date:- {{ $service agreement->signature->date->timezone($company->timezone) }}
                </p>
            </div>
        @endif
    </div>

   {{--Custom fields data--}}
   @if(isset($fields) && count($fields) > 0)
   <div class="page_break"></div>
       <h3 class="box-title m-t-20 text-center h3-border"> @lang('modules.sites.otherInfo')</h3>
       <table class="bg-white" border="0" cellspacing="0" cellpadding="0" width="100%" role="presentation">
           @foreach($fields as $field)
               <tr>
                   <td style="text-align: left;background: none;" >
                       <div class="f-14">{{ $field->label }}</div>
                       <p  class="f-14 line-height text-grey">
                           @if( $field->type == 'text' || $field->type == 'password' || $field->type == 'number' || $field->type == 'textarea')
                               {{$service agreement->custom_fields_data['field_'.$field->id] ?? '-'}}
                           @elseif($field->type == 'radio')
                               {{ !is_null($service agreement->custom_fields_data['field_'.$field->id]) ? $service agreement->custom_fields_data['field_'.$field->id] : '-' }}
                           @elseif($field->type == 'select')
                               {{ (!is_null($service agreement->custom_fields_data['field_'.$field->id]) && $service agreement->custom_fields_data['field_'.$field->id] != '') ? $field->values[$service agreement->custom_fields_data['field_'.$field->id]] : '-' }}
                           @elseif($field->type == 'checkbox')
                               {{ !is_null($service agreement->custom_fields_data['field_'.$field->id]) ? $service agreement->custom_fields_data['field_'.$field->id] : '-' }}
                           @elseif($field->type == 'date')
                               {{ !is_null($service agreement->custom_fields_data['field_'.$field->id]) ? \Carbon\Carbon::parse($service agreement->custom_fields_data['field_'.$field->id])->translatedFormat($service agreement->company->date_format) : '--'}}
                           @endif
                       </p>
                   </td>
               </tr>
           @endforeach
       </table>
   </div>

    @endif

</body>

</html>

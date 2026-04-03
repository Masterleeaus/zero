<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@lang('Form Request') - {{ $parkir->name }}</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="">
    {{-- {{ $company->favicon_url }} --}}
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

        * {
            font-family: Verdana, DejaVu Sans, sans-serif;
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

        .f-11 {
            font-size: 11px;
        }

        .f-13 {
            font-size: 13px;
        }

        .f-14 {
            font-size: 13px;
        }

        .f-15 {
            font-size: 13px;
        }

        .f-21 {
            font-size: 17px;
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

        .text-capitalize {
            text-transform: capitalize;
        }

        .line-height {
            line-height: 20px;
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
            color: #d30000;
            border: 1px solid #d30000;
            position: relative;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 0.25rem;
            width: 100px;
            text-align: center;
            margin-top: 50px;
        }

        .other {
            color: #000000;
            border: 1px solid #000000;
            position: relative;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 0.25rem;
            width: 120px;
            text-align: center;
            margin-top: 50px;
        }

        .paid {
            color: #28a745 !important;
            border: 1px solid #28a745;
            position: relative;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 0.25rem;
            width: 100px;
            text-align: center;
            margin-top: 50px;
        }

        .main-table-heading {
            border: 1px solid #DBDBDB;
            background-color: #f1f1f3;
            font-weight: 700;
        }

        .main-table-heading td {
            padding: 5px 8px;
            border: 1px solid #DBDBDB;
        }

        .main-table-items td {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
        }

        .total-box {
            border: 1px solid #e7e9eb;
            padding: 0px;
            border-bottom: 0px;
        }

        .subtotal {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
            border-right: 0;
        }

        .subtotal-amt {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
            border-right: 0;
        }

        .total {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            font-weight: 700;
            border-left: 0;
            border-right: 0;
        }

        .total-amt {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
            border-right: 0;
            font-weight: 700;
        }

        .balance {
            font-size: 14px;
            font-weight: bold;
            background-color: #f1f1f3;
        }

        .balance-left {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
            border-right: 0;
        }

        .balance-right {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
            border-top: 0;
            border-left: 0;
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

        #logo {
            height: 50px;
        }

        .word-break {
            max-width: 175px;
            word-wrap: break-word;
        }

        .summary {
            padding: 11px 10px;
            border: 1px solid #e7e9eb;
            font-size: 11px;
        }

        .border-left-0 {
            border-left: 0 !important;
        }

        .border-right-0 {
            border-right: 0 !important;
        }

        .border-top-0 {
            border-top: 0 !important;
        }

        .border-bottom-0 {
            border-bottom: 0 !important;
        }

        .h3-border {
            border-bottom: 1px solid #AAAAAA;
        }

        @if ($invoiceSetting->locale == 'th')

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
                <td class="f-21 text-black font-weight-700 text-uppercase" align="center">
                    @lang('Form data berlangganan parkir')<br>
                    <table class="text-black mt-1 f-13 b-collapse">
                        <tr>
                            <td class="heading-table-left">@lang('Nama Pemohon')</td>
                            <td class="heading-table-right">{{ ucwords($parkir->name) }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('Request Status')</td>
                            <td class="heading-table-right">{{ ucwords(str_replace('-', ' ', $parkir->status)) }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('Unit')</td>
                            <td class="heading-table-right">{{ ucwords($parkir->unit->unit_name) }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('No Handphone')</td>
                            <td class="heading-table-right">{{ ucwords($parkir->no_hp) }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('Nama Perusahaan')</td>
                            <td class="heading-table-right">{{ ucwords($parkir->company_name) }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">@lang('Request Type')</td>
                            <td class="heading-table-right">{{ ucwords(str_replace('-', ' ', $parkir->request)) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- Table Row End -->
        </tbody>
    </table>

    <table width="100%" class="f-14 b-collapse">
        <tr>
            <td height="10" colspan="2"></td>
        </tr>
        <!-- Table Row Start -->
        <tr class="main-table-heading text-grey">
            <td width="20%" class="border" align="left">
                @lang('Jenis Kendaraan')</td>
            <td width="20%" class="border" align="left">
                @lang('Jumlah Periode')</td>
            <td width="20%" class="border" align="left">
                @lang('Nomor Plat Lama')</td>
            <td width="20%" class="border" align="left">
                @lang('Nomor Plat Baru')</td>
            <td width="20%" class="border" align="left">
                @lang('Biaya')</td>
        </tr>
        <!-- Table Row End -->
        <!-- Table Row Start -->
        @foreach ($parkir->items as $item)
            <tr class="main-table-items text-black">
                <td width="20%">
                    {{ ucwords($item->jenis_kendaraan) }}
                </td>
                <td align="right" width="20%">
                    {{ $item->jumlah_periode }}
                </td>
                <td align="right" width="20%">
                    {{ $item->no_plat_lama }}
                </td>
                <td align="right" width="20%">
                    {{ $item->no_plat_baru }}
                </td>
                <td align="right" width="20%">
                    {{ $item->biaya }}
                </td>
            </tr>
            <!-- Table Row End -->
        @endforeach
        <tr class="main-table-items text-black">
            <td colspan="3" class="border-0"></td>
            <td class="bg-amt-grey" align="right">Sub Total</td>
            <td class="bg-amt-grey" align="right">{{ $total }}</td>
        </tr>
    </table>


</body>

</html>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@lang('Form Request') - {{ $card->name }}</title>
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
            <tr class="mb-2">
                <td><img src="{{ $invoiceSetting->logo_url }}" alt="{{ mb_ucwords($company->company_name) }}"
                        id="logo" /></td>

                <td align="right" class="f-21 text-black font-weight-700 text-uppercase">@lang('Form Penambahan Kartu Akses')<br>
                    <table class="text-black mt-1 f-13 b-collapse rightaligned">
                        <tr>
                            <td class="heading-table-left mb-2">@lang('Date')</td>
                            <td class="heading-table-right mb-2">{{ ucwords($card->date) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <p class="line-height mb-0"></p>
            <tr>
                <td class="f-14 heading-table-left text-black mt-3" colspan="2">
                    <span class="text-grey text-capitalize">@lang('Data Pemohon')</span>
                </td>
            </tr>
            <tr>
                <td class="f-14 text-black" width="28%">
                    <p class="line-height mb-0">
                        <span class="text-grey text-capitalize">@lang('Unit')</span>
                    </p>
                </td>
                <td class="f-14 text-black" width="82%">
                    <p class="line-height mb-0">
                        <span class="text-black text-capitalize">:
                            {{ '#' . $card->unit->unit_code . ' - ' . $card->unit->unit_name }}</span>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="f-14 text-black" width="28%">
                    <p class="line-height mb-0">
                        <span class="text-grey text-capitalize">@lang('Nama Pemilik / Penghuni')</span>
                    </p>
                </td>
                <td class="f-14 text-black" width="82%">
                    <p class="line-height mb-0">
                        <span class="text-black text-capitalize">: {{ ucwords($card->name) }}</span>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="f-14 text-black" width="28%">
                    <p class="line-height mb-0">
                        <span class="text-grey text-capitalize">@lang('No Handphone')</span>
                    </p>
                </td>
                <td class="f-14 text-black" width="82%">
                    <p class="line-height mb-0">
                        <span class="text-black text-capitalize">: {{ ucwords($card->no_hp) }}</span>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="f-14 text-black" width="28%">
                    <p class="line-height mb-0">
                        <span class="text-grey text-capitalize">@lang('Total Kartu')</span>
                    </p>
                </td>
                <td class="f-14 text-black" width="82%">
                    <p class="line-height mb-0">
                        <span class="text-black text-capitalize">: {{ ucwords($card->total_kartu) }}</span>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="f-14 text-black" width="28%">
                    <p class="line-height mb-0">
                        <span class="text-grey text-capitalize">@lang('Kartu Tambahan')</span>
                    </p>
                </td>
                <td class="f-14 text-black" width="82%">
                    <p class="line-height mb-0">
                        <span class="text-black text-capitalize">: {{ ucwords($card->kartu_tambahan) }}</span>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="f-14 text-black" width="28%">
                    <p class="line-height mb-0">
                        <span class="text-grey text-capitalize">@lang('No Kartu')</span>
                    </p>
                </td>
                <td class="f-14 text-black" width="82%">
                    <p class="line-height mb-0">
                        <span class="text-black text-capitalize">: {{ ucwords($card->no_kartu) }}</span>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="f-14 text-black" width="28%">
                    <p class="line-height mb-0">
                        <span class="text-grey text-capitalize">@lang('IFCA No')</span>
                    </p>
                </td>
                <td class="f-14 text-black" width="82%">
                    <p class="line-height mb-0">
                        <span class="text-black text-capitalize">: {{ ucwords($card->no_ifca) }}</span>
                    </p>
                </td>
            </tr>
            <!-- Table Row End -->
        </tbody>
    </table>
</body>

</html>

<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('app.quote')</title>
    @includeIf('quotes.pdf.estimate_pdf_css')
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            text-decoration: none;
        }

        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-size: 13px;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        h2 {
            font-weight: normal;
        }

        header {
            padding: 10px 0;
        }

        #logo img {
            height: 50px;
            margin-bottom: 15px;
        }

        #details {
            margin-bottom: 25px;
        }

        #customer {
            padding-left: 6px;
            float: left;
        }

        #customer .to {
            color: #777777;
        }

        h2.name {
            font-size: 1.2em;
            font-weight: normal;
            margin: 0;
        }

        #invoice h1 {
            color: #0087C3;
            line-height: 2em;
            font-weight: normal;
            margin: 0 0 10px 0;
            font-size: 20px;
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        }

        table {
            width: 100%;
            border-spacing: 0;
            /* margin-bottom: 20px; */
        }

        table th,
        table td {
            padding: 5px 8px;
            text-align: center;
        }

        table th {
            background: #EEEEEE;
        }

        table th {
            white-space: nowrap;
            font-weight: normal;
        }

        table td {
            text-align: right;
        }

        table td.desc h3,
        table td.qty h3 {
            font-size: 0.9em;
            font-weight: normal;
            margin: 0 0 0 0;
        }

        table .no {
            font-size: 0.9em;
            width: 10%;
            text-align: center;
            border-left: 1px solid #e7e9eb;
        }

        table .desc,
        table .item-summary {
            text-align: left;
        }

        table .unit {
            border: 1px solid #e7e9eb;
        }


        table .total {
            background: #57B223;
            color: #FFFFFF;
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1.2em;
            text-align: center;
        }

        table td.unit {
            width: 35%;
        }

        table td.desc {
            width: 45%;
        }

        table td.qty {
            width: 5%;
        }

        .status {
            margin-top: 15px;
            padding: 1px 8px 5px;
            font-size: 1.3em;
            width: 80px;
            float: right;
            text-align: center;
            display: inline-block;
        }

        .status.unpaid {
            background-color: #E7505A;
        }

        .status.paid {
            background-color: #26C281;
        }

        .status.cancelled {
            background-color: #95A5A6;
        }

        .status.error {
            background-color: #F4D03F;
        }

        table tr.tax .desc {
            text-align: right;
        }

        table tr.discount .desc {
            text-align: right;
            color: #E43A45;
        }

        table tr.subtotal .desc {
            text-align: right;
        }

        table tbody tr:last-child td {
            border: none;
        }

        table tfoot td {
            padding: 10px;
            font-size: 1.2em;
            white-space: nowrap;
            border-bottom: 1px solid #e7e9eb;
            font-weight: 700;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr td:first-child {
            /* border: none; */
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #0087C3;
        }

        #notices .notice {
            font-size: 1.2em;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #e7e9eb;
            padding: 8px 0;
            text-align: center;
        }

        table.billing td {
            background-color: #fff;
        }

        table td#invoiced_to {
            text-align: left;
            padding-left: 0;
        }

        #notes {
            color: #767676;
            font-size: 11px;
        }

        .item-summary {
            font-size: 11px;
            padding-left: 0;
        }

        .page_break {
            page-break-before: always;
        }


        table td.text-center {
            text-align: center;
        }

        .word-break {
            word-wrap: break-word;
            word-break: break-all;
        }

        #invoice-table td {
            border: 1px solid #e7e9eb;
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

        .f-13 {
            font-size: 13px;
        }

        .h3-border {
            border-bottom: 1px solid #AAAAAA;
        }

        .customer-logo {
            height: 50px;
            margin-bottom: 20px;
        }

        @if($invoiceSetting->locale=='th') table td {
            font-weight: bold !important;
            font-size: 20px !important;
        }

        .description {
            line-height: 12px;
            font-weight: bold !important;
            font-size: 16px !important;
        }
        @endif

    </style>

</head>
<body>
<header class="clearfix" class="description">

    <table cellpadding="0" cellspacing="0" class="billing">
        <tr>
            <td colspan="2"><h1>@lang('app.quote')</h1></td>
        </tr>
        <tr>
            <td id="invoiced_to">
                <div class="description">
                    <div>
                        @if (
                            ($quote->customer || $quote->clientDetails)
                            && ($quote->customer->name
                                || $quote->customer->email
                                || $quote->customer->mobile
                                || $quote->clientDetails->company_name
                                || $quote->clientDetails->address
                                )
                            && ($invoiceSetting->show_client_name == 'yes'
                            || $invoiceSetting->show_client_email == 'yes'
                            || $invoiceSetting->show_client_phone == 'yes'
                            || $invoiceSetting->show_client_company_name == 'yes'
                            || $invoiceSetting->show_client_company_address == 'yes')
                        )

                            @if($quote->clientDetails->company_logo)
                                <div class="customer-logo-div">
                                    <img src="{{ $quote->clientDetails->image_url }}"
                                         alt="{{ $quote->clientDetails->company_name }}" class="customer-logo"/>
                                </div>
                            @endif


                            <small>@lang("modules.invoices.billedTo"):</small>
                            <div class="mb-3">
                                @if ($quote->customer && $quote->customer->name && $invoiceSetting->show_client_name == 'yes')
                                    <b>{{ $quote->customer->name_salutation }}</b>
                                @endif
                                @if ($quote->customer && $quote->customer->email && $invoiceSetting->show_client_email == 'yes')
                                    <div>{{ $quote->customer->email }}</div>
                                @endif
                                @if ($quote->customer && $quote->customer->mobile && $invoiceSetting->show_client_phone == 'yes')
                                    <div>{{ $quote->customer->mobile_with_phonecode }}</div>
                                @endif
                                @if ($quote->clientDetails && $quote->clientDetails->company_name && $invoiceSetting->show_client_company_name == 'yes')
                                    <div>{{ $quote->clientDetails->company_name }}</div>
                                @endif
                                @if ($quote->clientDetails && $quote->clientDetails->address && $invoiceSetting->show_client_company_address == 'yes')
                                    <div>{!! nl2br($quote->clientDetails->address) !!}</div>
                                @endif
                            </div>
                        @endif

                        @if($invoiceSetting->show_gst == 'yes' && !is_null($quote->customer->clientDetails->gst_number))
                            <br><br><div> {{ $quote->customer->clientDetails->tax_name }}: {{ $quote->customer->clientDetails->gst_number }} </div><br><br>
                        @endif

                    </div>
            </td>
            <td>
                <div id="company" class="description">
                    <div id="logo">
                        <img src="{{ $invoiceSetting->logo_url }}" alt="home" class="dark-logo"/>
                    </div>
                    <small>@lang("modules.invoices.generatedBy"):</small>
                    <div>{{ $company->company_name }}</div>
                    @if(!is_null($company))
                        <div>{!! nl2br($company->defaultAddress->address) !!}</div>
                        <div>{{ $company->company_phone }}</div>
                    @endif
                    @if($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                        <br><br><div>{{ $invoiceSetting->tax_name }}: {{ $invoiceSetting->gst_number }}</div><br><br>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</header>
<main>
    <div id="details">

        <div id="invoice" class="description">
            <h1>{{ $quote->estimate_number }}</h1>
        </div>

    </div>
    @if ($quote->description)
        <div class="f-13 mb-3 description">{!! nl2br(pdfStripTags($quote->description)) !!}</div>
    @endif
    <table cellspacing="0" cellpadding="0" id="invoice-table">
        <thead>
        <tr>
            <th class="no">#</th>
            <th class="desc description">@lang("modules.invoices.item")</th>
            @if ($invoiceSetting->hsn_sac_code_show)
                <th class="qty description">@lang("app.hsnSac")</th>
            @endif
            <th class="qty description">@lang('modules.invoices.qty')</th>
            <th class="qty description">@lang("modules.invoices.unitPrice")</th>
            <th class="qty description">@lang("modules.invoices.tax")</th>
            <th class="unit description">@lang("modules.invoices.price")
                ({!! htmlentities($quote->currency->currency_code)  !!})
            </th>
        </tr>
        </thead>
        <tbody>
        <?php $count = 0; ?>
        @foreach($quote->items->sortBy('field_order') as $item)
            @if($item->type == 'item')
                <tr style="page-break-inside: avoid;">
                    <td class="no">{{ ++$count }}</td>
                    <td class="desc" width="40%">
                        <div style="width: 280px !important" class="word-break">
                            <h3 class="description word-break">{{ $item->item_name }}</h3>
                            @if(!is_null($item->item_summary))
                                <div class="item-summary description word-break">
                                    {!! nl2br(pdfStripTags($item->item_summary)) !!}
                                </div>
                            @endif
                            @if ($item->estimateItemImage)
                                <p class="mt-2">
                                    <img src="{{ $item->estimateItemImage->file_url }}" width="60" height="60"
                                         class="img-thumbnail">
                                </p>
                            @endif
                        </div>
                    </td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td class="qty"><h3>{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</h3></td>
                    @endif
                    <td class="qty"><h3>{{ $item->quantity }}@if($item->unit)
                                <br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>
                            @endif</h3></td>
                    <td class="qty"><h3>{{ currency_format($item->unit_price, $quote->currency_id, false) }}</h3>
                    </td>
                    <td>{{ $item->tax_list }}</td>
                    <td class="unit">{{ currency_format($item->amount, $quote->currency_id, false) }}</td>
                </tr>
            @endif
        @endforeach
        <tr style="page-break-inside: avoid;" class="subtotal">
            <td class="no">&nbsp;</td>
            <td class="qty">&nbsp;</td>
            <td class="qty">&nbsp;</td>
            <td class="qty">&nbsp;</td>
            @if($invoiceSetting->hsn_sac_code_show)
                <td class="qty">&nbsp;</td>
            @endif
            <td class="desc">@lang("modules.invoices.subTotal")</td>
            <td class="unit">{{ currency_format($quote->sub_total, $quote->currency_id, false) }}</td>
        </tr>
        @if($discount != 0 && $discount != '')
            <tr style="page-break-inside: avoid;" class="discount">
                <td class="no">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                @if($invoiceSetting->hsn_sac_code_show)
                    <td class="qty">&nbsp;</td>
                @endif
                <td class="desc">@lang("modules.invoices.discount"):
                    @if($quote->discount_type == 'percent')
                        {{$quote->discount}}%
                    @else
                        {{ currency_format($quote->discount, $quote->currency_id) }}
                    @endif
                </td>
                <td class="unit">{{ currency_format($discount, $quote->currency_id, false) }}</td>
            </tr>
        @endif
        @foreach($taxes as $key=>$tax)
            <tr style="page-break-inside: avoid;" class="tax">
                <td class="no">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                @if($invoiceSetting->hsn_sac_code_show)
                    <td class="qty">&nbsp;</td>
                @endif
                <td class="desc">{{ $key }}</td>
                <td class="unit">{{ currency_format($tax, $quote->currency_id, false) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr dontbreak="true">
            <td colspan="5">@lang("modules.invoices.total")</td>
            <td style="text-align: center">{{ currency_format($quote->total, $quote->currency_id, false) }}</td>
        </tr>
        </tfoot>
    </table>
    <p id="notes" class="word-break description">
        @if (!is_null($quote->note))
            @lang('app.note')
            <br>
            {!! nl2br($quote->note) !!}<br>
        @endif
        <br>@lang('modules.invoiceSettings.invoiceTerms')
        <br>
        {!! nl2br($invoiceSetting->invoice_terms) !!}
    </p>

    @if (isset($invoiceSetting->other_info))
        <p id="notes" class="word-break description">
            {!! nl2br($invoiceSetting->other_info) !!}
        </p>
    @endif

    @if (isset($taxes) && $invoiceSetting->tax_calculation_msg == 1)
        <p class="text-dark-grey description">
            @if ($quote->calculate_tax == 'after_discount')
                @lang('team chat.calculateTaxAfterDiscount')
            @else
                @lang('team chat.calculateTaxBeforeDiscount')
            @endif
        </p>
    @endif
    <div class="notes">
        @if ($quote->sign)
            <h5 style="margin-bottom: 20px;">@lang('app.signature')</h5>
            <img src="{{ $quote->sign->signature }}" style="height: 75px;">
            <p>({{ $quote->sign->full_name }})</p>
        @endif
    </div>
    {{--Custom fields data--}}
    @if(isset($fields) && count($fields) > 0)
        <div class="page_break"></div>
        <h3 class="box-title m-t-20 text-center h3-border "> @lang('modules.sites.otherInfo')</h3>
        <table style="background: none" border="0" cellspacing="0" cellpadding="0" width="100%">
            @foreach($fields as $field)
                <tr>
                    <td style="text-align: left;background: none;">
                        <div class="desc">{{ $field->label }} </div>
                        <p id="notes">
                            @if( $field->type == 'text' || $field->type == 'password' || $field->type == 'number' || $field->type == 'textarea')
                                {{$quote->custom_fields_data['field_'.$field->id] ?? '-'}}
                            @elseif($field->type == 'radio')
                                {{ !is_null($quote->custom_fields_data['field_'.$field->id]) ? $quote->custom_fields_data['field_'.$field->id] : '-' }}
                            @elseif($field->type == 'select')
                                {{ (!is_null($quote->custom_fields_data['field_'.$field->id]) && $quote->custom_fields_data['field_'.$field->id] != '') ? $field->values[$quote->custom_fields_data['field_'.$field->id]] : '-' }}
                            @elseif($field->type == 'checkbox')
                                {{ !is_null($quote->custom_fields_data['field_'.$field->id]) ? $quote->custom_fields_data['field_'.$field->id] : '-' }}
                            @elseif($field->type == 'date')
                                {{ !is_null($quote->custom_fields_data['field_'.$field->id]) ? \Carbon\Carbon::parse($quote->custom_fields_data['field_'.$field->id])->translatedFormat($quote->company->date_format) : '--'}}
                            @endif
                        </p>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
</main>
</body>
</html>

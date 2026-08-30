<!DOCTYPE html>
<html lang="en" dir="{{ $settings['site_rtl'] == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        {{ \Workdo\Invoice\Entities\Invoice::invoiceNumberFormat($invoice->invoice_id, $invoice->created_by, $invoice->workspace) }}
        |
        {{ !empty(company_setting('title_text', $invoice->created_by, $invoice->workspace)) ? company_setting('title_text', $invoice->created_by, $invoice->workspace) : (!empty(admin_setting('title_text')) ? admin_setting('title_text') : 'WorkDo') }}
    </title>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <style type="text/css">
        :root {
            --theme-color: {{ $color }};
            --white: #ffffff;
            --black: #000000;
            --border-color: #e5e7eb;
            --muted: #6b7280;
        }

        body {
            font-family: 'Lato', sans-serif;
            color: #1f2937;
        }

        p,
        li,
        ul,
        ol {
            margin: 0;
            padding: 0;
            list-style: none;
            line-height: 1.6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr th {
            padding: 0.75rem;
            text-align: left;
        }

        table tr td {
            padding: 0.75rem;
            text-align: left;
        }

        table th small {
            display: block;
            font-size: 11px;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            margin-top: 2px;
        }

        .invoice-preview-main {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd;
        }

        .invoice-logo {
            max-width: 190px;
            max-height: 90px;
            width: auto;
            height: auto;
        }

        .invoice-header {
            border-bottom: 4px solid rgba(0, 0, 0, 0.08);
        }

        .invoice-header table td {
            padding: 15px 30px;
        }

        .invoice-header table:first-of-type td {
            padding-bottom: 5px;
            vertical-align: middle;
        }

        .invoice-title {
            text-transform: uppercase;
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: var(--muted);
        }

        .no-space tr td {
            padding: 2px 0;
            white-space: nowrap;
        }

        .no-space tr td:first-child {
            color: var(--muted);
            padding-right: 12px;
        }

        .vertical-align-top td {
            vertical-align: top;
        }

        .view-qrcode {
            max-width: 139px;
            height: 139px;
            width: 100%;
            margin-left: auto;
            margin-top: 15px;
            background: var(--white);
            padding: 13px;
            border-radius: 10px;
        }

        .view-qrcode img {
            width: 100%;
            height: 100%;
        }

        .invoice-body {
            padding: 25px 25px 0;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            margin-bottom: 8px;
            display: block;
        }

        .bill-ship-row td {
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
        }

        .invoice-summary thead tr {
            border-bottom: 2px solid var(--theme-color, #1f2937);
        }

        .invoice-summary thead th {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #374151;
            background-color: rgba(0, 0, 0, 0.03);
        }

        .invoice-summary tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        .invoice-summary td {
            font-size: 13px;
        }

        .num-col {
            text-align: right;
            white-space: nowrap;
        }

        .itm-description td {
            padding-top: 0;
            padding-bottom: 14px;
            font-size: 12px;
        }

        .totals-wrap {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .totals-box {
            width: 100%;
            max-width: 300px;
        }

        .totals-box table tr td {
            padding: 6px 0;
            font-size: 13px;
        }

        .totals-box .grand-total td {
            border-top: 2px solid #1f2937;
            padding-top: 10px;
            font-size: 15px;
            font-weight: 700;
        }

        .totals-box .balance-due td {
            font-weight: 700;
            font-size: 15px;
            color: {{ $invoice->getDue() > 0 ? '#b91c1c' : '#15803d' }};
        }

        .info-section {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid var(--border-color);
        }

        .info-section:last-of-type {
            padding-bottom: 25px;
        }

        .info-section h4 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #374151;
            margin-bottom: 6px;
        }

        .info-section p {
            font-size: 13px;
            color: #374151;
            white-space: pre-wrap;
        }

        .border-0 {
            border: none !important;
        }

        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th {
            text-align: right;
        }

        html[dir="rtl"] .text-right,
        html[dir="rtl"] .num-col {
            text-align: left;
        }

        html[dir="rtl"] .view-qrcode {
            margin-left: 0;
            margin-right: auto;
        }

        html[dir="rtl"] .totals-wrap {
            justify-content: flex-start;
        }

        .wid-75 {
            width: 75px;
        }
    </style>
</head>

<body>
    <div class="invoice-preview-main" id="boxes">
        <div class="invoice-header" style="background-color: var(--theme-color); color: {{ $font_color }};">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <img class="invoice-logo" src="{{ $img }}" alt="">
                        </td>
                        <td class="text-right">
                            <h3 class="invoice-title">{{ __('INVOICE') }}</h3>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td>
                            @php
                                // Built as a list of only the lines that actually have
                                // content — the previous markup emitted a fixed run of
                                // unconditional <br> tags between fields, so a business
                                // with an incomplete profile got several blank lines of
                                // dead space here instead of the block just shrinking.
                                $companyLines = [];
                                if (!empty($settings['company_name'])) {
                                    $companyLines[] = $settings['company_name'];
                                }
                                if (!empty($settings['company_email'])) {
                                    $companyLines[] = $settings['company_email'];
                                }
                                if (!empty($settings['company_telephone'])) {
                                    $companyLines[] = $settings['company_telephone'];
                                }
                                if (!empty($settings['company_address'])) {
                                    $companyLines[] = $settings['company_address'];
                                }
                                $cityState = array_filter([$settings['company_city'] ?? null, $settings['company_state'] ?? null]);
                                if (!empty($cityState)) {
                                    $companyLines[] = implode(', ', $cityState);
                                }
                                $countryZip = array_filter([$settings['company_country'] ?? null, $settings['company_zipcode'] ?? null]);
                                if (!empty($countryZip)) {
                                    $companyLines[] = implode(' - ', $countryZip);
                                }
                                if (!empty($settings['registration_number'])) {
                                    $companyLines[] = __('Registration Number') . ': ' . $settings['registration_number'];
                                }
                                if (!empty($settings['tax_type']) && !empty($settings['vat_number'])) {
                                    $companyLines[] = $settings['tax_type'] . ' ' . __('Number') . ': ' . $settings['vat_number'];
                                }
                            @endphp
                            @if (!empty($companyLines))
                                <p>
                                    @foreach ($companyLines as $line)
                                        {{ $line }}@if (!$loop->last)
                                            <br>
                                        @endif
                                    @endforeach
                                </p>
                            @endif
                        </td>
                        <td style="width: 60%;">
                            <table class="no-space">
                                <tbody>
                                    <tr>
                                        <td>{{ __('Number') }}</td>
                                        <td class="text-right">
                                            {{ \Workdo\Invoice\Entities\Invoice::invoiceNumberFormat($invoice->invoice_id, $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Issue Date') }}</td>
                                        <td class="text-right">
                                            {{ company_date_formate($invoice->issue_date, $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Due Date') }}</td>
                                        <td class="text-right">
                                            {{ company_date_formate($invoice->due_date, $invoice->created_by, $invoice->workspace) }}
                                        </td>
                                    </tr>
                                    @if (!empty($customFields) && count($invoice->customField) > 0)
                                        @foreach ($customFields as $field)
                                            <tr>
                                                <td>{{ $field->name }}</td>
                                                <td class="text-right" style="white-space: normal;">
                                                    @if ($field->type == 'attachment')
                                                        <a href="{{ get_file($invoice->customField[$field->id]) }}" target="_blank">
                                                            <img src=" {{ get_file($invoice->customField[$field->id]) }} " class="wid-75 rounded me-3">
                                                        </a>
                                                    @else
                                                        <p>{{ !empty($invoice->customField[$field->id]) ? $invoice->customField[$field->id] : '-' }}</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (!empty($appointmentCustomFields))
                                        @foreach ($appointmentCustomFields as $fieldLabel => $fieldValue)
                                            <tr>
                                                <td>{{ $fieldLabel }}</td>
                                                <td class="text-right" style="white-space: normal;">
                                                    <p>{{ !empty($fieldValue) ? $fieldValue : '-' }}</p>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (isset($settings['invoice_qr_display']) && $settings['invoice_qr_display'] == 'on')
                                        <tr>
                                            @if (module_is_active('Zatca',$invoice->created_by))
                                                <td colspan="2">
                                                    <div class="view-qrcode">
                                                        @include('zatca::zatca_qr_code', [
                                                            'invoice_id' => $invoice->invoice_id,
                                                        ])
                                                    </div>
                                                </td>
                                            @else
                                                <td colspan="2">
                                                    <div class="view-qrcode">
                                                        {!! DNS2D::getBarcodeHTML(
                                                            route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)),
                                                            'QRCODE',
                                                            2,
                                                            2,
                                                        ) !!}
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="invoice-body">
            <table class="bill-ship-row">
                <tbody>
                    @if ($invoice->invoice_module != 'Fleet')
                    <tr>
                        @if (!empty($customer))
                            <td>
                                <span class="section-label">{{ __('Bill To') }}</span>
                                <p>
                                    <strong>{{ !empty($customer->billing_name) ? $customer->billing_name : (!empty($customer->name) ? $customer->name : '') }}</strong><br>
                                    @if (!empty($customer->email))
                                        {{ __('Email') }}: {{ $customer->email }}<br>
                                    @endif
                                    @if (!empty($customer->mobile_no))
                                        {{ __('Mobile') }}: {{ $customer->mobile_no }}<br>
                                    @endif
                                    @if (!empty($customer->billing_address))
                                        {{ $customer->billing_address }}<br>
                                        {{ !empty($customer->billing_city) ? $customer->billing_city . ' ,' : '' }}
                                        {{ !empty($customer->billing_state) ? $customer->billing_state . ' ,' : '' }}
                                        {{ !empty($customer->billing_zip) ? $customer->billing_zip : '' }}<br>
                                        {{ !empty($customer->billing_country) ? $customer->billing_country : '' }}<br>
                                    @endif
                                    @if (!empty($customer->billing_phone))
                                        {{ $customer->billing_phone }}<br>
                                    @endif
                                </p>
                            </td>
                        @endif
                        @if ($settings['shipping_display'] == 'on')
                            @if (!empty($customer->shipping_name) && !empty($customer->shipping_address) && !empty($customer->shipping_zip))
                                <td class="text-right">
                                    <span class="section-label">{{ __('Ship To') }}</span>
                                    <p>
                                        {{ !empty($customer->shipping_name) ? $customer->shipping_name : '' }}<br>
                                        {{ !empty($customer->shipping_address) ? $customer->shipping_address : '' }}<br>
                                        {{ !empty($customer->shipping_city) ? $customer->shipping_city . ' ,' : '' }}
                                        {{ !empty($customer->shipping_state) ? $customer->shipping_state . ' ,' : '' }}
                                        {{ !empty($customer->shipping_zip) ? $customer->shipping_zip : '' }}<br>
                                        {{ !empty($customer->shipping_country) ? $customer->shipping_country : '' }}<br>
                                        {{ !empty($customer->shipping_phone) ? $customer->shipping_phone : '' }}<br>
                                    </p>
                                </td>
                            @endif
                        @endif
                        @endif
                    </tr>
                        @if ($invoice->invoice_module == 'Fleet')
                            <tr>
                                <td style="font-size: 13px;">
                                    <span class="section-label">{{ __('Name') }}</span>
                                    {{ $commonCustomer['name'] }}
                                </td>
                                <td style="font-size: 13px;">
                                    <span class="section-label">{{ __('Email') }}</span>
                                    {{ $commonCustomer['email'] }}
                                </td>
                            </tr>
                        @endif
                </tbody>
            </table>
            <table class="invoice-summary" style="margin-top: 25px;">
                <thead>
                    <tr>
                        @if (in_array($invoice->invoice_module, ['account', 'appointment']))
                            <th>{{ __('Item Type') }}</th>
                        @endif
                        @if ($invoice->invoice_module == 'Fleet')
                            <th>{{ __('Distance') }}</th>
                        @endif
                        @if ($invoice->invoice_module != 'Fleet')
                            <th>{{ __('Item') }}</th>
                            <th class="num-col">{{ __('Quantity') }}</th>
                        @endif
                        <th class="num-col">{{ __('Rate') }}</th>
                        @if ($invoice->invoice_module == 'Fleet')
                            <th>{{ __('Discription') }}</th>
                        @endif
                        @if ($invoice->invoice_module != 'Fleet')
                            <th class="num-col">{{ __('Discount') }}</th>
                            <th class="num-col">{{ __('Tax') }} (%)</th>
                        @endif
                        <th class="num-col">{{ __('Price') }}<small>{{ __('After discount & tax') }}</small></th>

                    </tr>
                </thead>
                <tbody>
                    @if (isset($invoice->itemData) && count($invoice->itemData) > 0)
                    @foreach ($invoice->itemData as $key => $item)
                    <tr>
                        @if (in_array($invoice->invoice_module, ['account', 'appointment']))
                            <td>{{ !empty($item->product_type) ? Str::ucfirst($item->product_type) : '--' }}
                            </td>
                        @endif

                        @if ($invoice->invoice_module != 'Fleet')
                            <td>{{ $item->name }}</td>
                            <td class="num-col">{{ $item->quantity }}</td>
                        @endif
                        <td class="num-col">{{ currency_format_with_sym($item->price, $invoice->created_by, $invoice->workspace) }}
                        </td>
                        @if ($invoice->invoice_module == 'Fleet')
                            <th>{{ $item->description }}</th>
                        @endif
                        @if ($invoice->invoice_module != 'Fleet')
                            <td class="num-col">{{ $item->discount != 0 ? currency_format_with_sym($item->discount, $invoice->created_by, $invoice->workspace) : '-' }}
                            </td>
                            <td class="num-col">
                                @if (!empty($item->itemTax))
                                    @foreach ($item->itemTax as $taxes)
                                        <span>{{ $taxes['name'] }} ({{ $taxes['rate'] }})</span>
                                    @endforeach
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                        @endif

                        @if ($invoice->invoice_module == 'Fleet')
                            @php
                                $distance = !empty($item->name) ? $item->name : 0;
                                $price = $item->price * $item->name;
                            @endphp
                            <td class="num-col">{{ currency_format_with_sym($price, $invoice->created_by, $invoice->workspace) }}</td>
                        @else
                            <td class="num-col">{{ currency_format_with_sym($item->price * $item->quantity - $item->discount + (isset($item->tax_price) ? $item->tax_price : 0), $invoice->created_by, $invoice->workspace) }}
                            </td>
                        @endif
                        @if ($invoice->invoice_module != 'Fleet')
                            {{-- Only shown when it actually adds information — for a
                                 line item whose description was set to the same text
                                 as its name (a common default), repeating it verbatim
                                 right below just reads as a formatting glitch. --}}
                            @if (!empty($item->description) && trim($item->description) !== trim($item->name))
                                <tr class="border-0 itm-description ">
                                    <td colspan="6" class="text-muted">{{ $item->description }} </td>
                                </tr>
                            @endif
                        @endif
                @endforeach
                @else
                    <tr>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>
                            <p>-</p>
                            <p>-</p>
                        </td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="totals-wrap">
                <div class="totals-box">
                    <table>
                        @if ($invoice->invoice_module != 'Fleet')
                            <tr>
                                <td>{{ __('Subtotal') }}</td>
                                <td class="text-right">{{ currency_format_with_sym($invoice->getSubTotal(), $invoice->created_by, $invoice->workspace) }}
                                </td>
                            </tr>
                            @if ($invoice->getTotalDiscount())
                                <tr>
                                    <td>{{ __('Discount') }}</td>
                                    <td class="text-right">-{{ currency_format_with_sym($invoice->getTotalDiscount(), $invoice->created_by, $invoice->workspace) }}
                                    </td>
                                </tr>
                            @endif
                        @endif
                        @if (!empty($invoice->taxesData))
                            @foreach ($invoice->taxesData as $taxName => $taxPrice)
                                <tr>
                                    <td>{{ $taxName }}</td>
                                    <td class="text-right">{{ currency_format_with_sym($taxPrice, $invoice->created_by, $invoice->workspace) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr class="grand-total">
                            <td>{{ __('Total') }}</td>
                            @if ($invoice->invoice_module == 'Fleet')
                                <td class="text-right">{{ currency_format_with_sym($invoice->getFleetSubTotal(), $invoice->created_by, $invoice->workspace) }}</td>
                            @else
                                <td class="text-right">{{ currency_format_with_sym($invoice->getSubTotal() - $invoice->getTotalDiscount() + $invoice->getTotalTax(), $invoice->created_by, $invoice->workspace) }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td>{{ __('Paid') }}</td>
                            <td class="text-right">{{ currency_format_with_sym($invoice->getTotal() - $invoice->getDue(), $invoice->created_by, $invoice->workspace) }}
                            </td>
                        </tr>
                        @php
                            // invoiceTotalCustomerCreditNote() — not invoiceTotalCreditNote(),
                            // a different, unused entity that's always 0. A credit note here
                            // is store credit for a *future* invoice, so it's informational
                            // only and left out of the balance-due math below it.
                            $creditNoteIssued = $invoice->invoiceTotalCustomerCreditNote();
                        @endphp
                        @if ($creditNoteIssued > 0)
                            <tr>
                                <td>{{ __('Credit Note Issued') }}</td>
                                <td class="text-right">{{ currency_format_with_sym($creditNoteIssued, $invoice->created_by, $invoice->workspace) }}
                                </td>
                            </tr>
                        @endif
                        <tr class="balance-due">
                            <td>{{ __('Balance Due') }}</td>
                            <td class="text-right">{{ currency_format_with_sym($invoice->getDue(), $invoice->created_by, $invoice->workspace) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if (!empty($invoice->notes))
                <div class="info-section">
                    <h4>{{ __('Notes') }}</h4>
                    <p>{{ $invoice->notes }}</p>
                </div>
            @endif

            @if (!empty($settings['footer_title']) || !empty($settings['footer_notes']))
                <div class="info-section">
                    @if (!empty($settings['footer_title']))
                        <h4>{{ $settings['footer_title'] }}</h4>
                    @endif
                    @if (!empty($settings['footer_notes']))
                        <p>{{ $settings['footer_notes'] }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
    @if (!isset($preview))
        @include('invoice::invoice.script');
    @endif
</body>

</html>

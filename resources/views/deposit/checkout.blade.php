{{-- The customer-facing checkout page.

     Standalone rather than extending the admin layout: this page is opened from
     an SMS by somebody who is not logged in, and the admin layout's view
     composers all assume an authenticated company user. --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Pay your deposit') }} — {{ $businessName }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style>
        body { background: #f4f5f7; color: #1b1b1f; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .checkout { max-width: 460px; margin: 48px auto; }
        .card { border: 1px solid #e3e5ea; border-radius: 10px; background: #fff; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid #eef0f4; }
        .card-body { padding: 24px; }
        .amount { font-size: 2.1rem; font-weight: 600; letter-spacing: -0.02em; }
        .row-line { display: flex; justify-content: space-between; padding: 7px 0; }
        .row-line + .row-line { border-top: 1px solid #f2f3f6; }
        .muted { color: #6b7280; }
        .pay-btn { display: block; width: 100%; padding: 12px; border-radius: 8px; border: 0;
                   background: #0f5f5c; color: #fff; font-weight: 600; font-size: 1rem; cursor: pointer; }
        .pay-btn + .pay-btn { margin-top: 10px; }
        .notice { padding: 14px 16px; border-radius: 8px; margin-bottom: 18px; }
        .notice-good { background: #e2f0e7; color: #235437; }
        .notice-bad { background: #fae7e7; color: #7d1d1d; }
        .footer-note { text-align: center; color: #6b7280; font-size: 0.85rem; margin-top: 18px; }
        .terms { background: #f7f8fa; border: 1px solid #e9ebef; border-radius: 8px;
                 padding: 14px 16px; margin-bottom: 18px; font-size: 0.85rem; color: #4b5563; }
        .terms strong { display: block; color: #1b1b1f; margin-bottom: 6px; }
        .terms ul { margin: 0; padding-left: 18px; }
        .terms li { margin-bottom: 4px; }
        .terms li:last-child { margin-bottom: 0; }
    </style>
</head>
<body>
<div class="checkout">
    <div class="card">
        <div class="card-header">
            <div class="muted" style="font-size:0.85rem;">{{ $businessName }}</div>
            <h1 style="font-size:1.25rem;margin:4px 0 0;">{{ __('Appointment deposit') }}</h1>
        </div>
        <div class="card-body">

            @if (session('success'))
                <div class="notice notice-good">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="notice notice-bad">{{ session('error') }}</div>
            @endif

            @if ($invoice->isPaid())
                <div class="notice notice-good">
                    <strong>{{ __('Payment received — thank you.') }}</strong><br>
                    {{ __('Your appointment is confirmed. There is nothing more to do.') }}
                </div>
            @else
                <div style="text-align:center;margin-bottom:20px;">
                    <div class="muted" style="font-size:0.85rem;">{{ __('Amount due') }}</div>
                    <div class="amount">{{ $currency }}{{ number_format($invoice->outstanding(), 2) }}</div>
                </div>
            @endif

            <div style="margin-bottom:20px;">
                @if (!empty($customerName))
                    <div class="row-line">
                        <span class="muted">{{ __('Name') }}</span>
                        <span>{{ $customerName }}</span>
                    </div>
                @endif
                @if (!empty($services))
                    <div class="row-line">
                        <span class="muted">{{ count($services) > 1 ? __('Services') : __('Service') }}</span>
                        <span style="text-align:right;max-width:60%;">{{ implode(', ', $services) }}</span>
                    </div>
                @endif
                @if (!empty($appointment))
                    <div class="row-line">
                        <span class="muted">{{ __('Date') }}</span>
                        <span>{{ $appointment->date }}</span>
                    </div>
                    <div class="row-line">
                        <span class="muted">{{ __('Time') }}</span>
                        <span>{{ $appointment->time }}</span>
                    </div>
                    @if (!empty($appointment->deposit_message))
                        <div class="row-line">
                            <span class="muted">{{ __('Note') }}</span>
                            <span style="text-align:right;max-width:60%;">{{ $appointment->deposit_message }}</span>
                        </div>
                    @endif
                @endif
            </div>

            @if (!$invoice->isPaid())
                @if (empty($gateways))
                    {{-- Should be unreachable: raising a deposit is refused when no
                         gateway is ready. Kept as a plain explanation rather than a
                         dead page, in case a gateway is switched off after the link
                         was sent. --}}
                    <div class="notice notice-bad">
                        {{ __('Online payment is temporarily unavailable. Please contact the salon to pay your deposit.') }}
                    </div>
                @else
                    {{-- Terms sit above the pay buttons deliberately: the customer
                         has to have seen the forfeiture rule before they pay, not
                         after. --}}
                    <div class="terms">
                        <strong>{{ __('Before you pay') }}</strong>
                        <ul>
                            <li>{{ __('This deposit is applied to your final bill on the day.') }}</li>
                            @if ($forfeitHours > 0)
                                <li>{{ __('It is non-refundable if you do not attend, or if you cancel within :hours hours of your appointment.', ['hours' => $forfeitHours]) }}</li>
                            @else
                                <li>{{ __('It is non-refundable if you do not attend your appointment.') }}</li>
                            @endif
                            <li>{{ __('Your booking is confirmed as soon as the deposit is received.') }}</li>
                        </ul>
                    </div>

                    @foreach ($gateways as $slug => $gateway)
                        <form method="POST"
                            action="{{ route('deposit.pay.start', ['token' => $invoice->token, 'gateway' => $slug]) }}">
                            @csrf
                            <button type="submit" class="pay-btn">
                                {{ __('Pay with :gateway', ['gateway' => $gateway['label']]) }}
                            </button>
                        </form>
                    @endforeach
                @endif
            @endif

        </div>
    </div>
    <p class="footer-note">{{ __('Secure payment. Your card details are handled by the payment provider.') }}</p>
</div>
</body>
</html>

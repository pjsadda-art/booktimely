{{-- Shown when a pay link's token matches nothing.

     Deliberately vague about *why*: this page is public, so it must not confirm
     whether a given token ever existed. --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Link not found') }}</title>
    <style>
        body { background: #f4f5f7; color: #1b1b1f; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .box { max-width: 420px; margin: 96px auto; background: #fff; border: 1px solid #e3e5ea;
               border-radius: 10px; padding: 32px; text-align: center; }
        h1 { font-size: 1.2rem; margin: 0 0 10px; }
        p { color: #6b7280; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="box">
        <h1>{{ __('This payment link is no longer valid') }}</h1>
        <p>{{ __('It may have expired or already been used. Please contact the salon and they will send you a new one.') }}</p>
    </div>
</body>
</html>

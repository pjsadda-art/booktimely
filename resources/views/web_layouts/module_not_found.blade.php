<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $module . ' Not Found' }}</title>
    <link rel="stylesheet" href="{{ asset('module_assets/custom.css') }}">
</head>

<body>
    <div class="error">

        <section class="dedicated-themes-section">
            <div class="container">
                <div class="section-title text-center section">
                    <h1>{{ __('404') }}</h1>
                    <p>{{ __('Ooops!!! The Site you are looking for is not found') }}</p>
                </div>
            </div>
        </section>
    </div>
</body>

</html>

@extends('layouts.main')

@section('page-title')
    {{ __('Theme Customize') }}
@endsection

@section('page-breadcrumb')
    {{ __('Theme Customize') }},
    {{ Module_Alias_Name($id) }}
@endsection

@section('page-action')
@endsection

@push('css')

@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style setting-menu-div">
                    <h2 class="section-title">{{ __('All about theme settings') }}</h2>
                    <p class="section-lead">
                        {{ __('You can adjust all theme settings here') }}
                    </p>
                    <div class="row mt-4">
                        <div class="col-xxl-2 col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-header p-3">
                                    <h5>{{ __('Jump To Settings') }}</h5>
                                </div>
                                <div class="card-body p-3">
                                    <ul class="nav nav-pills flex-column gap-1">
                                        @foreach ($theme_json as $json_settings)
                                            <li class="nav-item"><a
                                                    href="{{ route('customize.edit', [$id, $json_settings['slug'], $json_settings['sections'][0]['slug'], $businessID]) }}"
                                                    class="nav-link {{ $json_settings['slug'] == $slug ? 'active' : '' }}">{{ $json_settings['title'] }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-lg-3  col-md-6 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-header p-3">
                                    <h5>{{ __('Jump To Settings') }}</h5>
                                </div>
                                <div class="card-body p-3">
                                    <ul class="nav nav-pills flex-column gap-1">
                                        @foreach ($theme_json as $json_settings)
                                            @if ($slug == $json_settings['slug'])
                                                @foreach ($json_settings['sections'] as $json_setting)
                                                    <li class="nav-item"><a
                                                            href="{{ route('customize.edit', [$id, $json_settings['slug'], $json_setting['slug'], $businessID]) }}"
                                                            class="nav-link {{ $json_setting['slug'] == $sub_slug ? 'active' : '' }}">{{ $json_setting['title'] }}</a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-8 col-lg-6 col-md-12 col-sm-12 col-12">
                            @foreach ($theme_json as $json_settings)
                                @if ($json_settings['slug'] == $slug)
                                    {{ Form::open(['route' => ['customize.update', [ $businessID, $id]], 'enctype' => 'multipart/form-data', 'id' => 'setting-form']) }}
                                    <div class="card" id="settings-card">
                                        <div class="card-header p-3">
                                            <h5>{{ $json_settings['title'] }} {{ __('Settings') }}</h5>
                                        </div>
                                        <div class="card-body p-3 pb-0">
                                            <p class="text-muted">{{ $json_settings['detail'] }}</p>
                                            @foreach ($json_settings['sections'] as $json_setting)
                                                @if ($json_setting['slug'] == $sub_slug)
                                                @foreach ($json_setting['settings'] as $key => $fields)
                                                        @switch($fields['type'])
                                                            @case('switch')
                                                                <div class="form-group">
                                                                    <div class="section-title"><h4>{{ $json_setting['title'] }}</h4></div>
                                                                    <div class="custom-control custom-radio custom-control-inline">
                                                                        <input type="radio"
                                                                            id="{{ $json_setting['key'] . '_' . $fields['key'] }}"
                                                                            name="{{ $json_setting['key'] . '_' . $fields['key'] }}"
                                                                            class="custom-control-input" value="1"
                                                                            @if (isset($themeSetting[$json_setting['key'] . '_' . $fields['key']]) &&
                                                                                    $themeSetting[$json_setting['key'] . '_' . $fields['key']] == '1') {{ 'checked' }}
                                                                            @elseif ($fields['value'] == '1')
                                                                                {{ 'checked' }} @endif>
                                                                        <label class="custom-control-label"
                                                                            for="{{ $json_setting['key'] . '_' . $fields['key'] }}">{{ __('On') }}</label>
                                                                    </div>
                                                                    <div class="custom-control custom-radio custom-control-inline">
                                                                        <input type="radio"
                                                                            id="{{ $json_setting['key'] . '_' . $fields['key'] }}2"
                                                                            name="{{ $json_setting['key'] . '_' . $fields['key'] }}"
                                                                            class="custom-control-input" value="0"
                                                                            {{ isset($themeSetting[$json_setting['key'] . '_' . $fields['key']]) && $themeSetting[$json_setting['key'] . '_' . $fields['key']] == '0' ? 'checked' : '' }}>
                                                                        <label class="custom-control-label"
                                                                            for="{{ $json_setting['key'] . '_' . $fields['key'] }}2">{{ __('Off') }}</label>
                                                                    </div>
                                                                </div>
                                                            @break

                                                            @case('textarea')
                                                                <div class="form-group">
                                                                    {!! Form::label($json_setting['key'] . '_' . $fields['key'], $fields['label'], [
                                                                        'class' => 'form-label',
                                                                    ]) !!}
                                                                    {!! Form::textarea(
                                                                        $json_setting['key'] . '_' . $fields['key'],
                                                                        isset($themeSetting[$json_setting['key'] . '_' . $fields['key']])
                                                                            ? $themeSetting[$json_setting['key'] . '_' . $fields['key']]
                                                                            : $fields['value'],
                                                                        [
                                                                            'id' => $json_setting['key'] . '_' . $fields['key'],
                                                                            'class' => 'form-control h-auto',
                                                                            'rows' => $fields['rows'] ? $fields['rows'] : 5,
                                                                            'placeholder' => $fields['placeholder'],
                                                                        ],
                                                                    ) !!}
                                                                </div>
                                                            @break

                                                            @case('text')
                                                                <div class="form-group">
                                                                    {!! Form::label($json_setting['key'] . '_' . $fields['key'], $fields['label'], [
                                                                        'class' => 'form-label',
                                                                    ]) !!}
                                                                    {!! Form::text(
                                                                        $json_setting['key'] . '_' . $fields['key'],
                                                                        isset($themeSetting[$json_setting['key'] . '_' . $fields['key']])
                                                                            ? $themeSetting[$json_setting['key'] . '_' . $fields['key']]
                                                                            : $fields['value'],
                                                                        [
                                                                            'id' => $json_setting['key'] . '_' . $fields['key'],
                                                                            'class' => 'form-control',
                                                                            'placeholder' => $fields['placeholder'],
                                                                        ],
                                                                    ) !!}
                                                                </div>
                                                            @break

                                                            @case('image')
                                                                <div class="form-group">
                                                                    <div class="choose-files mt-3">
                                                                        <img src="{{ isset($themeSetting[$json_setting['key'] . '_' . $fields['key']])
                                                                        ? get_file($themeSetting[$json_setting['key'] . '_' . $fields['key']])
                                                                        : get_file($fields['value']) }}" width="100"  height="100" class="me-2" >
                                                                        <label
                                                                            for="{{ $json_setting['key'] . '_' . $fields['key'], $fields['label'] }}">
                                                                            <div class="bg-primary ">
                                                                                <i
                                                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                            </div>
                                                                            <input type="file" class="form-control file"
                                                                                name="{{ $json_setting['key'] }}_image"
                                                                                id="{{ $json_setting['key'] . '_' . $fields['key'] }}"
                                                                                data-filename="{{ $json_setting['key'] . '_' . $fields['key'] }}">

                                                                        </label>

                                                                    </div>
                                                                    <input type="hidden" name="slug"
                                                                        value="{{ $json_setting['key'] }}">
                                                                </div>
                                                            @break

                                                            @case('menu')
                                                                <div class="form-group">
                                                                    <label for="{{ $json_setting['key'] . '_' . $fields['key'] }}"
                                                                        class="form-label">{{ $fields['label'] }}</label>
                                                                    <select id="{{ $json_setting['key'] . '_' . $fields['key'] }}"
                                                                        class="form-control"
                                                                        name="{{ $json_setting['key'] . '_' . $fields['key'] }}">
                                                                        @foreach ($menus as $id => $menu)
                                                                            <option value="{{ $id }}"
                                                                                @if (isset($themeSetting[$json_setting['key'] . '_' . $fields['key']]) &&
                                                                                        $themeSetting[$json_setting['key'] . '_' . $fields['key']] == $id) {{ 'selected' }} @elseif($fields['label'] == $id) {{ 'selected' }} @endif>
                                                                                {{ $menu }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            @break

                                                            @case('icon')
                                                                <div class="form-group">
                                                                    {!! Form::label($json_setting['key'] . '_' . $fields['key'], $fields['label'], [
                                                                        'class' => 'form-label',
                                                                    ]) !!}
                                                                    <div class="form-group">
                                                                        <div class="mb-2 input-group">
                                                                            <div class="input-group-prepend">
                                                                                <div class="input-group-text iconpicker-component">
                                                                                    <i class="@if (isset($themeSetting[$json_setting['key'] . '_' . $fields['key']]))  {{ $themeSetting[$json_setting['key'] . '_' . $fields['key']] }} @else {{ $fields['class'] }} @endif"></i>
                                                                                </div>
                                                                                {!! Form::button(
                                                                                    Form::hidden(
                                                                                        $json_setting['key'] . '_' . $fields['key'],
                                                                                        isset($themeSetting[$json_setting['key'] . '_' . $fields['key']])
                                                                                            ? $themeSetting[$json_setting['key'] . '_' . $fields['key']]
                                                                                            : $fields['class'],
                                                                                        ['id' => ''],
                                                                                    ),
                                                                                    [
                                                                                        'class' => 'icp icp-dd btn bg-whight btn-outline-light text-dark dropdown-toggle',
                                                                                        'data-placement' => 'bottomLeft',
                                                                                        'data-selected' => $fields['class'],
                                                                                        'data-bs-toggle' => 'dropdown',

                                                                                    ],
                                                                                ) !!}
                                                                                <div class="dropdown-menu"></div>
                                                                            </div>
                                                                            {!! Form::text(
                                                                                $json_setting['key'] . '_' . $fields['key'] . '_title',
                                                                                isset($themeSetting[$json_setting['key'] . '_' . $fields['key'] . '_title'])
                                                                                    ? $themeSetting[$json_setting['key'] . '_' . $fields['key'] . '_title']
                                                                                    : $fields['value'],
                                                                                [
                                                                                    'id' => $json_setting['key'] . '_' . $fields['key'],
                                                                                    'class' => 'form-control',
                                                                                    'placeholder' => $fields['placeholder'],
                                                                                ],
                                                                            ) !!}
                                                                        </div>
                                                                    </div>
                                                                    @if (isset($fields['text']))
                                                                        {!! Form::label($json_setting['key'] . '_' . $fields['key'] . '_text', $fields['label'] . ' ' . __('Text'), [
                                                                            'class' => 'form-label',
                                                                        ]) !!}
                                                                        {!! Form::text(
                                                                            $json_setting['key'] . '_' . $fields['key'] . '_text',
                                                                            isset($themeSetting[$json_setting['key'] . '_' . $fields['key'] . '_text'])
                                                                                ? $themeSetting[$json_setting['key'] . '_' . $fields['key'] . '_text']
                                                                                : $fields['text'],
                                                                            ['id' => '', 'class' => 'form-control'],
                                                                        ) !!}
                                                                    @endif
                                                                </div>
                                                            @break

                                                            @case('brand_carousel')
                                                                <div class="repeater1">
                                                                    <div data-repeater-list="{{ $json_setting['key'] . '_' . $fields['key'] }}">
                                                                        <div data-repeater-item>
                                                                            <div class="row align-items-center">
                                                                                <div class="col-10">
                                                                                    <div class="form-group">
                                                                                        <div class="choose-files mt-3">
                                                                                            <label for="image">
                                                                                                <div class="bg-primary ">
                                                                                                    <i
                                                                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                                                </div>
                                                                                                <input type="file"
                                                                                                    class="form-control file"
                                                                                                    name="image" id="image"
                                                                                                    data-filename="image">
                                                                                            </label>
                                                                                            <img src="" width="100"
                                                                                                height="100">

                                                                                            <input type="hidden" name="image"
                                                                                                class="selected-files">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="text-center col-2">
                                                                                    <div class="action-btn">
                                                                                        <a href="#" class="btn btn-sm bg-danger" data-repeater-delete>
                                                                                            <i class="text-white ti ti-trash"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-0">
                                                                        <p id="repeaters-data" data-json='{!! isset($themeSetting[$json_setting['key'] . '_' . $fields['key']]) ? $themeSetting[$json_setting['key'] . '_' . $fields['key']] : "" !!}' class="d-none">

                                                                        </p>
                                                                        <button type="button" class="btn btn-outline-primary align-items-center"
                                                                            data-repeater-create>
                                                                            <i class="ti ti-plus me-1"></i>
                                                                            <span
                                                                               >{{ __('Add Banner') }}</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @break

                                                            @case('banner')
                                                                <div class="repeater">
                                                                    <div data-repeater-list="{{ $json_setting['key'] . '_' . $fields['key'] }}">
                                                                        <div data-repeater-item>
                                                                            <div class="row align-items-center">
                                                                                <div class="col-10">
                                                                                    <div class="form-group">
                                                                                        <div class="choose-files mt-3">
                                                                                            <label for="image">
                                                                                                <div class="bg-primary ">
                                                                                                    <i
                                                                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                                                                </div>
                                                                                                <input type="file"
                                                                                                    class="form-control file"
                                                                                                    name="image" id="image"
                                                                                                    data-filename="image">
                                                                                            </label>
                                                                                            <img src="" width="100"
                                                                                            height="100">
                                                                                            <input type="hidden" name="image"
                                                                                                class="selected-files">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="col-6">
                                                                                            <div class="form-group">
                                                                                                {!! Form::label('form-repeater-1-1', 'Small Text', ['class' => 'form-label']) !!}
                                                                                                {!! Form::text('small_text', null, [
                                                                                                    'id' => 'form-repeater-1-1',
                                                                                                    'class' => 'form-control',
                                                                                                    'placeholder' => __('Enter Text'),
                                                                                                ]) !!}
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <div class="form-group">
                                                                                                {!! Form::label('form-repeater-1-2', 'Big Text', ['class' => 'form-label']) !!}
                                                                                                {!! Form::text('big_text', null, [
                                                                                                    'id' => 'form-repeater-1-2',
                                                                                                    'class' => 'form-control',
                                                                                                    'placeholder' => __('Enter Text'),
                                                                                                ]) !!}
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <div class="form-group">
                                                                                                {!! Form::label('form-repeater-1-6', 'Content', ['class' => 'form-label']) !!}
                                                                                                {!! Form::textarea('content', null, [
                                                                                                    'id' => 'form-repeater-1-2',
                                                                                                    'class' => 'form-control',
                                                                                                    'rows' => '3',
                                                                                                    'placeholder' => __('Enter Text'),
                                                                                                ]) !!}
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <div class="form-group">
                                                                                                {!! Form::label('form-repeater-1-3', 'Button text', ['class' => 'form-label']) !!}
                                                                                                {!! Form::text('button_text', null, [
                                                                                                    'id' => 'form-repeater-1-3',
                                                                                                    'class' => 'form-control',
                                                                                                    'placeholder' => __('Enter Text'),
                                                                                                ]) !!}
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="text-center col-2">
                                                                                    <div class="action-btn me-2">
                                                                                        <button type="button"
                                                                                        class="btn btn-sm bg-danger"
                                                                                        data-repeater-delete>
                                                                                        <i class="text-white ti ti-trash"></i>
                                                                                    </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <hr>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-0">
                                                                        <p id="repeater-data" data-json='{!! isset($themeSetting[$json_setting['key'] . '_' . $fields['key']]) ? $themeSetting[$json_setting['key'] . '_' . $fields['key']] : "" !!}' class="d-none">
                                                                            {!! isset($themeSetting['banner_repeater']) ? $themeSetting['banner_repeater'] : '' !!}
                                                                        </p>
                                                                        <button type="button" class="btn btn-outline-primary align-items-center"
                                                                            data-repeater-create>
                                                                            <i class="ti ti-plus me-1"></i>
                                                                            <span>{{ __('Add Banner') }}</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @break

                                                            @case('select_category')
                                                                <div class="form-group">
                                                                    {!! Form::label($json_setting['key'] . '_' . $fields['key'], $fields['label'], [
                                                                        'class' => 'form-label',
                                                                    ]) !!}
                                                                    @php $is_multiple = ($fields['multiple']) ? '[]' : ''; @endphp
                                                                    {!! Form::select(
                                                                        $json_setting['key'] . '_' . $fields['key'] . $is_multiple,
                                                                        $categories,
                                                                        isset($themeSetting[$json_setting['key'] . '_' . $fields['key']])
                                                                            ? explode(',', $themeSetting[$json_setting['key'] . '_' . $fields['key']])
                                                                            : [],
                                                                        [
                                                                            'class' => 'form-control select2',
                                                                            'id' => $json_setting['key'] . '_' . $fields['key'],
                                                                            'multiple' => $fields['multiple'],
                                                                            'data-placeholder' => __('Select an option'),
                                                                        ],
                                                                    ) !!}
                                                                    @if ($fields['multiple'])
                                                                        <small
                                                                            class="form-text text-muted">{{ __('Leave blank for show all active categories.') }}</small>
                                                                    @endif
                                                                </div>
                                                            @break

                                                            @case('meta_keywords')
                                                                <div class="form-group">
                                                                    {!! Form::label($json_setting['key'] . '_' . $fields['key'], $fields['label'], [
                                                                        'class' => 'form-label',
                                                                    ]) !!}
                                                                    @php $is_multiple = ($fields['multiple']) ? '[]' : ''; @endphp
                                                                    {!! Form::select(
                                                                        $json_setting['key'] . '_' . $fields['key'] . $is_multiple,
                                                                        $map_area_meta_keywords,
                                                                        array_keys($map_area_meta_keywords),
                                                                        [
                                                                            'class' => 'form-control select2',
                                                                            'data-tags' => 'true',
                                                                            'id' => $json_setting['key'] . '_' . $fields['key'],
                                                                            'multiple' => $fields['multiple'],
                                                                            'data-placeholder' => __('Select an option'),
                                                                        ],
                                                                    ) !!}
                                                                </div>
                                                            @break

                                                            @case('date')
                                                                <div class="form-group">
                                                                    {!! Form::label($json_setting['key'] . '_' . $fields['key'], $fields['label'], [
                                                                        'class' => 'form-label',
                                                                    ]) !!}
                                                                    {!! Form::text(
                                                                        $json_setting['key'] . '_' . $fields['key'],
                                                                        Carbon\Carbon::createFromTimestampMs($themeSetting[$json_setting['key'] . '_' . $fields['key']])->format(
                                                                            Utility::getsettings('date_format'),
                                                                        ),
                                                                        ['class' => 'form-control date-input', 'placeholder' => 'Select Date', 'readonly' => 'readonly'],
                                                                    ) !!}
                                                                </div>
                                                            @break

                                                            @default
                                                        @endswitch
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="card-footer p-3 bg-whitesmoke text-md-right">
                                            {!! Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            "use restrict"
            $(document).ready(function() {
                $('.icp-dd').iconpicker({
                    title: "Select Icon",
                    selected:false,
                    defaultValue: 'fa-heart'
                });
                $('.icp').on('iconpickerSelected', function(e) {
                    $(this).find('input').val(e.iconpickerValue);
                });
            });
            var field_count,
                count,
                $repeater = $(".repeater");
            $repeater.length &&
                ((field_count = 2),
                    (count = 1))
            $repeater.repeater({
                initEmpty: true,
                show: function() {
                    var data = $(this).find('input,textarea,select').toArray();
                    data.forEach(function(val) {
                        $(val).parent().find('label').attr('for', $(val).attr('name'));
                        $(val).attr('id', $(val).attr('name'));
                    });
                    var image = $(this).find('input[type="hidden"]').attr('name');
                    let convertedString = image.replace(/\[|\]/g, '_').replace(/_/g, '').replace(/_+/g, '_');
                    var img = $(this).find('img').addClass(convertedString);
                    $(this).slideDown();
                },
                hide: function(e) {
                    confirm('{{ __('Are you sure you want to delete this element ?') }}') && $(this)
                        .slideUp(e);
                },
            });
            var input_repeater = ($('#repeater-data').attr('data-json')) ? $('#repeater-data').attr('data-json') : "[]";
            input_repeater = JSON.parse(input_repeater);
            if ($repeater.length > 0) {
                $repeater.setList(input_repeater);
                $.each(input_repeater, function(key, item){
                    var imgSrc = item.image;
                    var list = $repeater.find('[data-repeater-list]').attr('data-repeater-list');
                    let liast = list.replace(/\[|\]/g, '_').replace(/_/g, '').replace(/_+/g, '_');
                    var imgClasses = liast + key + 'image';
                    $.ajax({
                    url: '{{ route('file.get') }}',
                    method: 'POST',
                    data: { imgSrc: imgSrc },
                    success: function(response) {
                        // Update the image source after getting the URL from the server
                        $('.' + imgClasses).attr('src', response);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
                });
            }

            var field_count,
                count,
                $repeater1 = $(".repeater1");
            $repeater1.length &&
                ((field_count = 2),
                    (count = 2))
            $repeater1.repeater({
                initEmpty: true,
                show: function() {
                    var data = $(this).find('input,textarea,select').toArray();
                    data.forEach(function(val) {
                        $(val).parent().find('label').attr('for', $(val).attr('name'));
                        $(val).attr('id', $(val).attr('name'));
                    });
                    var image = $(this).find('input[type="hidden"]').attr('name');
                    let convertedString = image.replace(/\[|\]/g, '_').replace(/_/g, '').replace(/_+/g, '_');
                    var img = $(this).find('img').addClass(convertedString);
                    $(this).slideDown();
                },
                hide: function(e) {
                    confirm('{{ __('Are you sure you want to delete this element?') }}') && $(this)
                        .slideUp(e);
                },
            });

            var input_repeater = ($('#repeaters-data').attr('data-json')) ? $('#repeaters-data').attr('data-json') : "[]";
            input_repeater = JSON.parse(input_repeater);
            if ($repeater1.length > 0) {
                $repeater1.setList(input_repeater);
                $.each(input_repeater, function(key, item) {
                    var imgSrc = item.image; // Assuming image_url is the property that holds the image URL
                    var list = $repeater1.find('[data-repeater-list]').attr('data-repeater-list');
                    let liast = list.replace(/\[|\]/g, '_').replace(/_/g, '').replace(/_+/g, '_');
                    var imgClass = liast + key + 'image';
                    $.ajax({
                    url: '{{ route('file.get') }}',
                    method: 'POST',
                    data: { imgSrc: imgSrc },
                    success: function(response) {
                        $('.' + imgClass).attr('src', response);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
                });
            }
        });
    </script>
@endpush

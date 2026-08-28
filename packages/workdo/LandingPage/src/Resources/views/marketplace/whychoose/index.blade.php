@extends('layouts.main')

@section('page-title')
    {{ __('Marketplace') }}
@endsection

@section('page-breadcrumb')
    {{ __('Marketplace') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            @include('landing-page::marketplace.modules')
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">

                            @include('landing-page::marketplace.tab', [$modules])

                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    {{--  Start for all settings tab --}}
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <h5>{{ __('Why Choose Section') }}</h5>
                                </div>
                            </div>
                        </div>
                        {{ Form::open(['route' => ['whychoose_store', $slug], 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
                        <div class="card-body">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                        {{ Form::text('whychoose_heading', $settings['whychoose_heading'], ['class' => 'form-control ', 'placeholder' => __('Enter Heading'), 'required' => 'required']) }}
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                                        {{ Form::textarea('whychoose_description', $settings['whychoose_description'], ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'id' => 'whychoose_description', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="row">
                                        <div class="py-3">
                                            <h5 class="mb-0">{{ __('Pricing Plan Section') }}</h5>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Form::text('pricing_plan_heading', $settings['pricing_plan_heading'], ['class' => 'form-control ', 'placeholder' => __('Enter Heading'), 'required' => 'required']) }}
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('pricing_plan_description', $settings['pricing_plan_description'], ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'id' => 'pricing_plan_description', 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                {{ Form::label('Live Demo button Link', __('Live Demo button Link'), ['class' => 'form-label']) }}
                                                {{ Form::text('pricing_plan_demo_link', $settings['pricing_plan_demo_link'], ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('Live Demo Button Text', __('Live Demo Button Text'), ['class' => 'form-label']) }}
                                                {{ Form::text('pricing_plan_demo_button_text', $settings['pricing_plan_demo_button_text'], ['class' => 'form-control', 'placeholder' => __('Enter Button Text'), 'required' => 'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="border p-3 rounded pb-0 overflow-hidden">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        <h5 class="mb-0">{{ __('Plan Features') }}</h5>
                                                    </div>
                                                    <div class="col-auto text-end">
                                                        <button id="add-cards-details"
                                                            class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" data-bs-original-title="Add Titles"
                                                            title="{{ __('Add Titles') }}">
                                                            <i class="ti ti-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                @if (isset($settings['pricing_plan_text']) && !empty($settings['pricing_plan_text']))
                                                    @foreach (json_decode($settings['pricing_plan_text'], true) as $key => $title)
                                                        <div id="{{ 'add-cards' . $key }}" class="border-bottom row py-2">
                                                            <div class="col-md-10">
                                                                <div class="form-group">
                                                                    {{ Form::label('Title', __('Title'), ['class' => 'form-label']) }}
                                                                    {{ Form::text('pricing_plan_text[' . $key . '][title]', $title['title'], ['class' => 'form-control', 'placeholder' => __('Enter title'), 'required' => 'required']) }}
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="col-md-2 d-flex text-center align-items-center justify-content-end">
                                                                <a href="#" id="{{ 'delete-card' . $key }}"
                                                                    class="card-delete btn btn-danger btn-sm bs-pass-para"
                                                                    title="{{ __('Delete') }}"
                                                                    data-title="{{ __('Delete') }}"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    data-bs-original-title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div id="add-cards1" class="border-bottom row py-2">
                                                        <div class="col-md-10">
                                                            <div class="form-group">
                                                                {{ Form::label('Title', __('Title'), ['class' => 'form-label']) }}
                                                                {{ Form::text('pricing_plan_text[1][title]', null, ['class' => 'form-control', 'placeholder' => __('Enter title'), 'required' => 'required']) }}
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="col-md-2 d-flex text-center align-items-center justify-content-end">
                                                            <a href="#" id="{{ 'delete-card1' }}"
                                                                class="card-delete btn btn-danger btn-sm bs-pass-para"
                                                                title="{{ __('Delete') }}"
                                                                data-title="{{ __('Delete') }}" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-original-title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice btn-primary" type="submit"
                                value="{{ __('Save Changes') }}">
                        </div>
                        {{ Form::close() }}
                    </div>

                    {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
@push('scripts')
    <script>
        $("#add-cards-details").click(function(e) {
            e.preventDefault()

            // get the last DIV which ID starts with ^= "another-participant"
            var $div = $('div[id^="add-cards"]:last');

            // Read the Number from that DIV's ID (i.e: 1 from "another-participant1")
            // And increment that number by 1
            var num = parseInt($div.prop("id").match(/\d+/g), 10) + 1;

            // Clone it and assign the new ID (i.e: from num 4 to ID "another-participant4")
            var $klon = $div.clone().prop('id', 'add-cards' + num);

            $klon.find('a').each(function() {
                this.id = "delete-card" + num;
            });

            // for each of the inputs inside the dive, clear it's value and
            // increment the number in the 'name' attribute by 1
            $klon.find('input').each(function() {
                this.value = "";
                let name_number = this.name.match(/\d+/);
                name_number++;
                this.name = this.name.replace(/\[[0-9]\]+/, '[' + name_number + ']')
            });
            // Finally insert $klon after the last div
            $div.after($klon);
        });

        $(document).on('click', '.card-delete', function(e) {
            e.preventDefault()
            var id = $(this).attr('id');
            var num = parseInt(id.match(/\d+/g), 10);
            var card = document.getElementById("add-cards" + num);
            if (num != 1) {
                card.remove();
            }
        });
    </script>
@endpush

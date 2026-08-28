{{ Form::open(['route' => ['flexibledays.store', 'staff_id' => $staff->user_id], 'class'=>'needs-validation','novalidate','enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pills-flexible_days_hours-tab" data-bs-toggle="pill" href="#pills-flexible_days_hours" role="tab" aria-controls="pills-flexible_days_hours" aria-selected="true">{{ __('Staff Flexible Timing') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pills-flexible_days-tab" data-bs-toggle="pill" href="#pills-flexible_days" role="tab" aria-controls="pills-flexible_days" aria-selected="false">{{ __('Flexible Days') }}</a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane " id="pills-flexible_days" role="tabpanel" aria-labelledby="pills-flexible_days-tab">
            <div class="row business-hrs">
                <div class="col-sm-12">

                    <div class="repeater_first" data-value="{{ !empty($flexible_data['json_encode_data']) ? $flexible_data['json_encode_data'] : '' }}">
                        <table class="table  mb-0" data-repeater-list="flexible_days">
                            <tbody class="repeater_first_tbody" data-repeater-item>
                                <tr>
                                    <td>
                                        <input type="date" name="date" class="form-control repeaterFirstDate" required="required">
                                    </td>
                                    <td>
                                        <input type="time" name="start_time" class="form-control repeaterFirstTime" required="required">
                                    </td>
                                    <td>
                                        <input type="time" name="end_time" class="form-control repeaterFirstTime" required="required">
                                    </td>
                                    <td>
                                        <div class="action-btn repeater-action-btn action-btn me-2">
                                            <a href="#" class="btn btn-sm bg-danger bs-pass-para repeater-action-btn  repeater_first_delete" title="{{ __('Delete Day') }}" style="cursor: pointer;" data-repeater-delete>
                                                <i class="text-white ti ti-trash"></i>
                                            </a>
                                        </div>
                                        <input type="hidden" name="id" class="recored_id">
                                    </td>
                                </tr>
                                <tr class="add_break_tr">
                                    <td>
                                        <div class="add-break-btn m-0 add-break" style="cursor: pointer;display:none;">
                                            <a href="#" class="bs-pass-para break_add p-0" title="{{ __('Add Break') }}" style="cursor: pointer;">
                                                <i class="fas fa-plus-circle"></i>
                                               <span>{{ __('Add Break') }}</span>
                                            </a>
                                        </div>
                                        <input type="hidden" name="flexible_day_id" value="10" class="flexible_day_id">
                                    </td>
                                    <td class="break_content">

                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <span class="add_days_btn d-inline-block " style="cursor: pointer;">
                            <span data-repeater-create="">
                                <button class="btn btn-primary" title="{{ __('Add Days') }}" type="button"><i class="fas fa-plus-circle"></i><span> {{ __('Add Days') }}</span></button>
                            </span>
                        </span>
                    </div>
                    {{-- hours end --}}
                </div>
            </div>
        </div>
        <div class="tab-pane fade show active" id="pills-flexible_days_hours" role="tabpanel" aria-labelledby="pills-flexible_days_hours-tab">
            <div class="row business-hrs">
                <div class="col-sm-12">
                    @php
                    $days = Workdo\FlexibleDays\Entities\FlexibleStaffHours::$weekdays;
                    @endphp
                    <div class="card mb-0">
                        <div class="table-responsive custom-scrollbar">
                        @foreach ($days as $index => $day)
                        <div class="">
                            @php
                            $data = Workdo\FlexibleDays\Entities\FlexibleStaffHours::dayWiseData($day, $staff->business_id,$staff->user_id);
                            @endphp
                            <table class="table  mb-0">
                                <tbody class="">
                                    <tr>
                                        <td>
                                            <h5 class="text-primary mb-0 capitalize">
                                                {{ ++$index . '.' . $day }}
                                            </h5>
                                        </td>
                                        <td>
                                            <input type="time" name="{{ $day }}[start]" value="{{ !empty($data['start_time']) ? $data['start_time'] : '' }}" class="form-control day_disable_{{ $index }}" {{ (!empty($data['day_off']) ? $data['day_off'] : '') == 'on' ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="time" name="{{ $day }}[end]" value="{{ !empty($data['end_time']) ? $data['end_time'] : '' }}" class="form-control day_disable_{{ $index }}" {{ (!empty($data['day_off']) ? $data['day_off'] : '') == 'on' ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <div class="form-control">
                                                <input class="form-check-input day_off" type="checkbox" data-id={{ $index }} name="{{ $day }}[day_off]" {{ (!empty($data['day_off']) ? $data['day_off'] : '') == 'on' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="inlineFormCheck ">
                                                    {{ __(' Add day off') }}
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- hours end --}}

                        {{-- repeater start --}}

                        <div class="repeaters day_off_{{ $index }} {{ (!empty($data['day_off']) ? $data['day_off'] : '') == 'on' ? 'd-none' : '' }} {{ $day }}_break_business" id="{{ $day }}_break_business" data-value="{{ !empty($data['break_hours']) ? $data['break_hours'] : '' }}">
                            <table class="table  mb-0" data-repeater-list="{{ $day }}[repeater]">
                                <tbody class="" data-repeater-item>
                                    <tr>
                                        <td>
                                            <p class="text-danger">
                                                {{ __('Break') }}
                                            </p>
                                        <td>
                                            <input type="time" name="start"  required="required" class="form-control repeaterTimeField">
                                        </td>
                                        <td>
                                            <input type="time" name="end" required="required" class="form-control repeaterTimeField">
                                        </td>
                                        <td>
                                            <div class="action-btn repeater-action-btn action-btn me-2">
                                                <a href="#" class="btn btn-sm bg-danger bs-pass-para repeater-action-btn" style="cursor: pointer;" data-repeater-delete>
                                                    <i class="text-white ti ti-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="add-break-btn mt-2 mb-3" style="cursor: pointer;">
                                <span data-repeater-create="">
                                    <i class="fas fa-plus-circle"></i>
                                    {{ __('Add break') }}
                                </span>
                            </div>
                        </div>
                        {{-- repeater end --}}
                        @endforeach
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="modal-footer gap-3">
    <button type="button" class="btn m-0 btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    {{Form::submit(__('Create'),array('class'=>'btn m-0 btn-primary'))}}
</div>
{{ Form::close() }}

<div id="flexibleday_break" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLiveLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading">{{ __('Add break') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="flexible_break_form" class='needs-validation novalidate'>
                <div class="modal-body">
                    <input type="hidden" name="flexible_days_id" id="flexible_days_id">
                    <input type="hidden" name="flexible_break_id" id="flexible_break_id">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="inputEmail4">{{ __('Start Time') }}</label>
                            <input type="time" class="form-control" required="required" name="break_start" id="break_start">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label" for="inputPassword4">{{ __('End Time') }}</label>
                            <input type="time" class="form-control" required="required" name="break_end" id="break_end">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn  btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn  btn-primary" id="saveBtn">{{__('Save Changes')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        var selector = "body";
        var slides = document.getElementsByClassName("repeaters");
        const flexible_staff_days = ["monday_break_business", "tuesday_break_business", "wednesday_break_business",
            "thursday_break_business", "friday_break_business", "saturday_break_business", "sunday_break_business"
        ];

        for (var i = 0; i < slides.length; i++) {
            var repeaterDayClass = '.' + flexible_staff_days[i];
            var $dragAndDrop = $("body .repeaters tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeaterFlexibleDaysHours = $(repeaterDayClass).repeater({
                initEmpty: true,

                hide: function(deleteElement) {
                    $(this).remove();
                },
                ready: function(setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },

                isFirstItemUndeletable: false
            });
            var value = $(repeaterDayClass).attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeaterFlexibleDaysHours.setList(value);
            }
            $(document).on('click', '[data-repeater-create]', function() {
                $(document).on('click', '.repeaterTimeField', function() {
                    $(this).attr('type', 'time');
                });
            });

        }

        $(document).on('click', '.day_off', function() {
            var data_id = '.day_off_' + $(this).data('id');
            var id = '.day_disable_' + $(this).data('id');
            let isChecked = $(this).is(':checked')

            if (isChecked) {
                $(data_id).addClass("d-none");
                $(id).prop('disabled', true);

            } else {
                $(data_id).removeClass("d-none");
                $(id).prop('disabled', false);
            }
        });

        $(document).on('click', '.repeaterTimeField', function() {
            $(this).attr('type', 'time');
        });
    });
</script>
<script>
    var dtToday = new Date();
    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if (month < 10)
        month = '0' + month.toString();
    if (day < 10)
        day = '0' + day.toString();

    var maxDate = year + '-' + month + '-' + day;
    $('.repeaterFirstDate').attr('min', maxDate);

    $(document).ready(function() {
        var $repeaterFlexibleDays = $('.repeater_first').repeater({
            initEmpty: false,
            defaultValues: {
                'status': 1
            },
            show: function() {
                $(this).slideDown();
                var fileUploads = $(this).find('input.multi');
                if (fileUploads.length) {
                    fileUploads.MultiFile({
                        max: 3,
                        accept: 'png|jpg|jpeg',
                        max_size: 2048
                    });
                }
            },
            hide: function(deleteElement) {

                $(this).slideUp(deleteElement);
                $(this).remove();
            },
            ready: function(setIndexes) {
                $(this).find('.repeater-list').sortable({
                    handle: '.sort-handler',
                    update: setIndexes
                });
            },
            isFirstItemUndeletable: true
        });
        var value = $('.repeater_first').attr('data-value');
        if (typeof value != 'undefined' && value.length != 0) {
            value = JSON.parse(value);
            $repeaterFlexibleDays.setList(value);
            var flexibleBreakData = <?php echo json_encode($flexible_data['flexible_breaks']); ?>;
            var flexible_day = $('.recored_id').val();
            flexible_day ? $('.add-break').show() : '';
            if (flexibleBreakData) {
                flexibleBreakData.forEach(function(item) {
                    var breakTime = JSON.parse(item.break_hours);
                    var $recoredIdInput = $('.recored_id[value="' + item.flexible_days_id + '"]');
                    var $nextTr = $recoredIdInput.closest('tr').next('tr').find('.break_content');;


                    $nextTr.append("<div class='btn-group btn-group-sm add_break_div'><button type='button' class='btn btn-info break' data-flexible_id='" + item.id + "' title='{{ __('Edit Day') }}' data-ssd_break_id='2' data-ssd_id='101'>" + breakTime.start + " - " + breakTime.end + " </button><button type='button' title='{{ __('Delete break') }}' data-id=" + item.id + " class='btn btn-info delete-break'><span class='ladda-label'>×</span></button></div>");
                });
            }

        }
    });
    $(document).on('click', '.repeater_first_delete', function() {
        var staff_id = $(this).closest('tr').find('.recored_id').val();
        if (staff_id) {
            if (confirm('Are you sure you want to delete this element?')) {
                $.ajax({
                    url: "{{route('flexibledays.delete')}}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        'staff_id': staff_id
                    },
                    cache: false,
                    success: function(data) {
                        if (data.success) {
                            toastrs('Success', data.success, 'success');
                        }
                    },
                    error: function(data) {
                        toastrs('Error', "{{ __('something went wrong please try again') }}", 'error');
                    },
                });
            }
        }

    });
</script>
<script>
    $(document).ready(function() {
        // Reset form on modal hide
        $("#flexibleday_break").on('hide.bs.modal', function() {
            $('#flexible_break_form').trigger("reset");
        });

        // Handle click on add break button
        $(document).on('click', '.break_add', function() {
            var flexible_days_id = $(this).closest('tr').parent().find('.recored_id').val();
            if (flexible_days_id) {
                $('#flexibleday_break').modal('show');
                $('#modelHeading').html("Add Break Time");
                $('#flexible_days_id').val(flexible_days_id);
                $('#flexible_break_id').val('');
            } else {

                toastrs('Error', "{{ __('Something went wrong. Please try again.') }}", 'error');
            }
        });

        // Handle click on edit break button
        $(document).on('click', '.break', function() {
            var flexible_break_id = $(this).attr('data-flexible_id');
            var editRoute = "{{ route('flexibleday.breaks.edit', ':flexible_break_id') }}";
            editRoute = editRoute.replace(':flexible_break_id', flexible_break_id);

            $.get(editRoute, function(data) {
                var breakTime = JSON.parse(data.break_hours);
                $('#flexible_days_id').val(data.flexible_days_id);
                $('#flexible_break_id').val(data.id);
                $('#break_start').val(breakTime.start);
                $('#break_end').val(breakTime.end);
                $('#modelHeading').html("Edit Break Time");
                $('#flexibleday_break').modal('show');
            });
        });

        // Handle form submission
        $('#flexible_break_form').submit(function(e) {
            e.preventDefault();
            var breaks = {
                start: $('#break_start').val(),
                end: $('#break_end').val(),
            };
            var flexible_break_id = $('#flexible_break_id').val();
            var formData = {
                break: breaks,
                flexible_days_id: $('#flexible_days_id').val(),
                flexible_break_id: flexible_break_id,
            }
            $.ajax({
                type: 'POST',
                url: "{{route('flexibleday.breaks.store')}}",
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.flexible_break) {
                        if (flexible_break_id) {
                            var $breakBtn = $('.break[data-flexible_id="' + response.flexible_break.id + '"]');
                            var breakTime = JSON.parse(response.flexible_break.break_hours);
                            $breakBtn.text(breakTime.start + " - " + breakTime.end + "");
                        } else {
                            var $recoredIdInput = $('.recored_id[value="' + response.flexible_break.flexible_days_id + '"]');
                            var $nextTr = $recoredIdInput.closest('tr').next('tr').find('.break_content');
                            var breakTime = JSON.parse(response.flexible_break.break_hours);

                            $nextTr.append("<div class='btn-group btn-group-sm add_break_div'><button type='button' class='btn btn-info break' data-flexible_id='" + response.flexible_break.id + "' title='{{ __('Edit Day') }}'>" + breakTime.start + " - " + breakTime.end + " </button><button type='button' title='{{ __('Delete break') }}' class='btn btn-info delete-break' data-id='" + response.flexible_break.id + "'><span class='ladda-label'>×</span></button></div>");

                            toastrs('Success', response.success, 'success');
                        }
                        $('#flexible_break_form').trigger("reset");
                        $('#flexibleday_break').modal('hide');
                    } else {
                        toastrs('Error', response.error, 'error');
                        $('#flexible_break_form').trigger("reset");
                        $('#flexibleday_break').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    toastrs('Error', "{{ __('Something went wrong. Please try again.') }}", 'error');
                }
            });
        });

        // Handle click on delete break button
        $('body').on('click', '.delete-break', function() {
            var flexible_break_id = $(this).data("id");
            var deleteRoute = "{{ route('flexibleday.breaks.destroy', ':flexible_break_id') }}";
            deleteRoute = deleteRoute.replace(':flexible_break_id', flexible_break_id);
            if (confirm("Are you sure you want to delete?")) {
                $.ajax({
                    type: "GET",
                    url: deleteRoute,
                    success: function(data) {
                        if (data.success) {
                            var $breakBtn = $('.break[data-flexible_id="' + flexible_break_id + '"]')
                            $breakBtn.closest('.add_break_div').remove();
                            toastrs('Success', data.success, 'success');
                        }
                    },
                    error: function(data) {
                        toastrs('Error', "{{ __('Something went wrong. Please try again.') }}", 'error');
                    }
                });
            }
        });
    });
</script>

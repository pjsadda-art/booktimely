<footer class="dash-footer">
    <div class="footer-wrapper">
        <div class="py-1">
            <span class="text-muted">
                @if (isset($company_settings['footer_text']))
                    {{ $company_settings['footer_text'] }}
                @elseif(isset($admin_settings['footer_text']))
                    {{ $admin_settings['footer_text'] }}
                @else
                    {{ __('Copyright') }} &copy; {{ config('app.name', 'BookingGo') }}
                @endif
                {{ date('Y') }}
            </span>
        </div>
    </div>
</footer>



@if (Route::currentRouteName() !== 'chatify')
    <div id="commonModal" class="modal" tabindex="-1" aria-labelledby="exampleModalLongTitle" aria-modal="true"
        role="dialog" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>
    <div id="commonModalCustomer" class="modal" tabindex="-1" aria-labelledby="exampleModalLongTitle" aria-modal="true"
        role="dialog" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="commonModalOver" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>
@endif
<div class="loader-wrapper d-none">
    <span class="site-loader"> </span>
</div>
<div class="position-fixed top-0 end-0 p-3 part-in">
    <div id="liveToast" class="toast text-white  fade" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"> </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>
{{-- create business modal --}}
<script>
    $('a').on("click", function() {
        var createBusinessTexts = [
            "Create Business", // English
            "إنشاء الأعمال", // Arabic
            "Opret forretning", // Danish
            "Unternehmen gründen", // german
            "crear negocio", // Spanish
            "Créer une entreprise", // french
            "Creare affari", // italian
            "ビジネスの創造", // japanese
            "Creëer zaken", // dutch
            "Stwórz biznes", // polish
            "Criar negócios", // portugues
            "Создать бизнес", // russian
            "İş Yarat" // turkish
        ];
        var linkText = $(this).text().trim();
        var isCreateBusiness = createBusinessTexts.some(function(text) {
            return linkText.includes(text);
        });
        if (isCreateBusiness) {
            var url = '{{ route('business.create') }}';
            var title = "{{ __('Create New Business') }}";
            var size = 'xl';

            $("#commonModal .modal-title").html(title);
            $("#commonModal .modal-dialog").addClass('modal-' + size);
            $.ajax({
                url: url,
                beforeSend: function() {
                    $(".loader-wrapper").removeClass('d-none');
                },
                success: function(data) {
                    $(".loader-wrapper").addClass('d-none');
                    $('#commonModal .body').html(data);
                    $("#commonModal").modal('show');
                    summernote();
                    taskCheckbox();
                    common_bind("#commonModal");
                },
                error: function(xhr) {
                    $(".loader-wrapper").addClass('d-none');
                    toastrs('Error', xhr.responseJSON.error, 'error')
                }
            });
        }
    });
</script>
{{-- create business modal --}}

<!-- Required Js -->
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('js/jquery.form.js') }}"></script>
<script src="{{ asset('assets/js/dash.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/choices.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/socialSharing.js') }}"></script>
<script src="{{ asset('assets/js/repeater.js') }}"></script>
<script src="{{ asset('assets/js/plugins/datepicker-full.js') }}"></script>

<script src="{{ asset('assets/js/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>

<script src="{{ asset('js/fontawesome-iconpicker.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>

<script src="{{ asset('js/icons.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>
@if ($message = Session::get('success'))
    <script>
        toastrs('Success', '{!! $message !!}', 'success');
    </script>
@endif
@if ($message = Session::get('error'))
    <script>
        toastrs('Error', '{!! $message !!}', 'error');
    </script>
@endif
@stack('scripts')
@if (isset($admin_settings['enable_cookie']) && $admin_settings['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
@endif
{{-- custom-js --}}
<script type="text/javascript">
    {!! isset($admin_settings['custom_js']) ? htmlspecialchars_decode($admin_settings['custom_js']) : '' !!}
</script>
</body>

</html>

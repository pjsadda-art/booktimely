{{-- One panel failed to load. The rest of the profile still renders — this page
     is what staff open when something has already gone wrong, so it must never
     go down as a whole. The failure is in the application log. --}}
<div class="alert alert-warning mb-0">
    <i class="ti ti-alert-triangle me-1"></i>
    {{ __('This section could not be loaded. The rest of the profile is unaffected, and the error has been logged.') }}
</div>

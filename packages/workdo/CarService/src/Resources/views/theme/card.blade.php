<div class="col-xxl-3 col-lg-4 col-md-6 col-sm-6 col-12 business-view-card business-view d-flex">
    <label for="carservice">
        <input type="radio" id="carservice" name="layouts" value="CarService">
        <div class="business-view-inner d-flex flex-column mb-0 h-100">
            <div class="buisness-img">
                <img src="{{ get_module_card_img('CarService') }}" alt="form" width="100%">
            </div>
            <div class="d-flex flex-wrap buisness-card-content align-items-end">
                <div class="buisness-title-wrp d-flex flex-wrap align-items-center justify-content-between w-100">
                    <div class="buisness-card-title">
                        <h6 class="mb-0">{{ Module_Alias_Name('CarService') }}</h6>
                    </div>
                    @if ($btn)
                        <div>
                            <a href="{{ route('business.customize', ['CarService', $businessId]) }}" class="btn btn-sm btn-primary">
                                {{ __('Customize') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </label>
</div>

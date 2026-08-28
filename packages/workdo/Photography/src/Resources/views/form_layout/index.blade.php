@extends('web_layouts.app')

@section('content')
    <!-- header-style-one start here -->
    <header class="site-header header-style-one">
        <div class="main-navigationbar sticky-header" id="header-sticky">
            <div class="container">
                <div class="navigationbar-row flex align-center justify-between">
                    @if (isset($themeSetting['logo_status']) && $themeSetting['logo_status'] == '1')
                        <div class="logo-col">
                            <h1>
                                <a href="#" tabindex="0">
                                    <img src="{{ isset($themeSetting['logo_image']) ? get_file($themeSetting['logo_image']) : asset('packages/workdo/Photography/src/Resources/assets/images/logo.png') }}"
                                        alt="logo" loading="lazy">
                                </a>
                            </h1>
                        </div>
                    @endif
                    @if (isset($themeSetting['menu_status']) && $themeSetting['menu_status'] == '1')
                        <div class="menu-item-left">
                            <nav class="menu-items-col">
                                <ul class="main-nav flex align-center">
                                    <li class="menu-lnk has-item">
                                        <a href="#home" class="click-btn"
                                            tabindex="0">{{ isset($themeSetting['menu_title_1']) ? $themeSetting['menu_title_1'] : __('Home') }}</a>
                                    </li>
                                    <li class="menu-lnk has-item">
                                        <a href="#about" class="click-btn"
                                            tabindex="0">{{ isset($themeSetting['menu_title_2']) ? $themeSetting['menu_title_2'] : __('about us') }}</a>
                                    </li>
                                    <li class="menu-lnk has-item">
                                        <a href="#service" class="click-btn"
                                            tabindex="0">{{ isset($themeSetting['menu_title_3']) ? $themeSetting['menu_title_3'] : __('our services') }}</a>
                                    </li>
                                    <li class="menu-lnk has-item">
                                        <a href="#our-team"
                                            tabindex="0">{{ isset($themeSetting['menu_title_4']) ? $themeSetting['menu_title_4'] : __('our team') }}</a>
                                    </li>
                                    <li class="menu-lnk">
                                        <a href="#portfolio"
                                            tabindex="0">{{ isset($themeSetting['menu_title_5']) ? $themeSetting['menu_title_5'] : __('portfolio') }}</a>
                                    </li>
                                    <li class="menu-lnk">
                                        <a href="#article" tabindex="0">
                                            {{ isset($themeSetting['menu_title_6']) ? $themeSetting['menu_title_6'] : __('blog') }}</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div class="menu-item-right">
                            <ul class="flex align-center">
                                <li class="contact-btn">
                                    <a href="#contact-us" class="btn justify-center" tabindex="0">
                                        <span>{{ isset($themeSetting['menu_title_7']) ? $themeSetting['menu_title_7'] : __('contact us') }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24"
                                            viewBox="0 0 25 24" fill="none">
                                            <path
                                                d="M23.4648 16.875C23.4008 16.8242 18.75 13.4719 17.4734 13.7125C16.8641 13.8203 16.5156 14.2359 15.8164 15.068C15.7039 15.2023 15.4336 15.5242 15.2234 15.7531C14.7816 15.609 14.3505 15.4336 13.9336 15.2281C11.7817 14.1805 10.043 12.4418 8.99531 10.2898C8.78974 9.87298 8.61429 9.44193 8.47031 9C8.7 8.78906 9.02188 8.51875 9.15938 8.40312C9.9875 7.70781 10.4039 7.35937 10.5117 6.74844C10.7328 5.48281 7.38281 0.8 7.34766 0.757812C7.19566 0.54068 6.9973 0.360049 6.76693 0.228987C6.53656 0.0979247 6.27994 0.0197095 6.01562 0C4.65781 0 0.78125 5.02891 0.78125 5.87578C0.78125 5.925 0.852344 10.9281 7.02187 17.2039C13.2914 23.3664 18.2938 23.4375 18.343 23.4375C19.1906 23.4375 24.2188 19.5609 24.2188 18.2031C24.1993 17.9397 24.1215 17.684 23.9911 17.4543C23.8608 17.2246 23.681 17.0268 23.4648 16.875Z"
                                                fill="white"></path>
                                        </svg>
                                    </a>
                                </li>
                                <li class="mobile-menu">
                                    <div class="mobile-menu-button btn open-menu" onclick="myFunction(this)">
                                        <div class="one"></div>
                                        <div class="two"></div>
                                        <div class="three"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>
    <!-- header-style-one end here -->

    <main class="wrapper">
        <!-- home-banner-sec -->
        <section class="home-banner-sec pt pb" id="home">
            <img src="{{ asset('packages/workdo/Photography/src/Resources/assets/images/home-design.png') }}" alt="design-1"
                class="home-design desk-only" loading="lazy">
            <div class="container-offset offset-right">
                <div class="banner-top">
                    <div class="swiper swiper-container  gallery-main">
                        <div class="swiper-wrapper">
                            @if (isset($themeSetting['banner_status']) && $themeSetting['banner_status'] == '1')
                                @if (count(json_decode($themeSetting['banner_repeater'])) > 0)
                                    @foreach (json_decode($themeSetting['banner_repeater']) as $banner)
                                        <div class="swiper-slide">
                                            <div class="row align-center gallery-main-row">
                                                <div class="col-md-6 col-12">
                                                    <div class="product-item-img">
                                                        <img src="{{ isset($banner->image) ? get_file($banner->image) : asset('packages/workdo/Photography/src/Resources/assets/images/home-main.png') }}"
                                                            alt="Product-image" loading="lazy">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="banner-content">
                                                        <div class="banner-content-inner">
                                                            <div class="section-title">
                                                                <div class="subtitle">
                                                                    {{ isset($banner->small_text) ? $banner->small_text : __('PERFECT MOOD IS ALWAYS HERE') }}
                                                                </div>
                                                                <h2>{{ isset($banner->big_text) ? $banner->big_text : __('Capture Your Moments Here') }}
                                                                </h2>
                                                            </div>
                                                            <p>{{ isset($banner->content) ? $banner->content : __('At our studio, were not just snapping photos were weaving stories. With heart and hustle, we turn your moments into magic. Lets make memories together!') }}
                                                            </p>
                                                            <a href="#appointment" class="btn btn-white"
                                                                tabindex="0">{{ isset($banner->button_text) ? $banner->button_text : __('Book an Appointment') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="banner-bottom">
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <div class="arrow-wrapper flex align-center">
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>
                            </div>
                        </div>
                        @if (isset($themeSetting['working-hours_status']) && $themeSetting['working-hours_status'] == '1')
                            <div class="col-md-4 col-12">
                                <div class="working-hrs-wrp">
                                    <div class="section-title">
                                        <h3 class="h5">
                                            {{ isset($themeSetting['working-hours_working_title']) ? $themeSetting['working-hours_working_title'] : __('ENABLE WORKING HOURS') }}
                                        </h3>
                                    </div>
                                    <ul>
                                        @foreach ($workingDays as $workingDay)
                                            <li>
                                                <span>{{ ucfirst($workingDay->day_name) }}</span>
                                                <p class="{{ $workingDay->day_off == 'on' ? 'close' : '' }}  ">
                                                    {{ $workingDay->day_off == 'on' ? 'Close' : date('H:i', strtotime($workingDay->start_time)) . ' to ' . date('H:i', strtotime($workingDay->end_time)) }}
                                                </p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- photo-about-sec -->
        @if (isset($themeSetting['about_status']) && $themeSetting['about_status'] == '1')
            <section class="photo-about-sec pt pb" id="about">
                <div class="container">
                    <div class="row align-center">
                        <div class="col-md-7 col-12">
                            <div class="photo-about-left-wrp">
                                <div class="section-title">
                                    <div class="subtitle">
                                        {{ isset($themeSetting['about_title']) ? $themeSetting['about_title'] : __('about US') }}
                                    </div>
                                    <h2>{{ isset($themeSetting['about_sub_title']) ? $themeSetting['about_sub_title'] : __('We love high quality photo products') }}
                                    </h2>
                                    <p>{{ isset($themeSetting['about_content']) ? $themeSetting['about_content'] : __('Dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernaturaut odit aut fugit, sed quia.') }}
                                    </p>
                                </div>
                                <ul class="counter-wrp">
                                    <li class="counter-item">
                                        <span>{{ isset($themeSetting['about_label_1']) ? $themeSetting['about_label_1'] : __('Years') }}
                                        </span>
                                        <div class="counting"
                                            data-count="{{ isset($themeSetting['about_value_1']) ? $themeSetting['about_value_1'] : '12' }}">
                                            {{ __('0') }}</div>
                                    </li>
                                    <li class="counter-item">
                                        <span>{{ isset($themeSetting['about_label_2']) ? $themeSetting['about_label_2'] : __('People') }}</span>
                                        <div class="counting"
                                            data-count="{{ isset($themeSetting['about_value_2']) ? $themeSetting['about_value_2'] : '100' }}">
                                            {{ __('0') }}</div>
                                    </li>
                                    <li class="counter-item">
                                        <span>{{ isset($themeSetting['about_label_3']) ? $themeSetting['about_label_3'] : __('Order') }}</span>
                                        <div class="counting"
                                            data-count="{{ isset($themeSetting['about_value_3']) ? $themeSetting['about_value_3'] : '350' }}">
                                            {{ __('0') }}</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-5 col-12">
                            <div class="photo-about-right-wrp">
                                <img src="{{ asset('packages/workdo/Photography/src/Resources/assets/images/design-1.png') }}"
                                    alt="design-1" class="design-1 desk-only" loading="lazy">
                                <img src="{{ asset('packages/workdo/Photography/src/Resources/assets/images/design-2.png') }}"
                                    alt="design-2" class="design-2 desk-only" loading="lazy">
                                <img src="{{ isset($themeSetting['about_image']) ? get_file($themeSetting['about_image']) : asset('packages/workdo/Photography/src/Resources/assets/images/about-photo.png') }}"
                                    alt="about-photo" class="about-photo" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- photo-service-sec -->
        <section class="photo-service-sec pt" id="service">
            <div class="container">
                @if (isset($themeSetting['service_status']) && $themeSetting['service_status'] == '1')
                    <div class="section-title">
                        <h2>{{ isset($themeSetting['service_title']) ? $themeSetting['service_title'] : __('WE ARE SERVICES PROVIDED') }}
                        </h2>
                        <p>{{ isset($themeSetting['service_content']) ? $themeSetting['service_content'] : __('Choose from our range of photobooth options, including open-air and enclosed booths, to suit your wedding theme and style.') }}
                        </p>
                    </div>
                @endif
                <div class="row">
                    @foreach ($services as $service)
                        <div class="col-md-6 col-12">
                            <div class="photo-card">
                                <div class="photo-card-image">
                                    <a href="#" class="img-wrapper" tabindex="0">
                                        <img src="{{ check_file($service->image) ? get_file($service->image) : get_file('uploads/default/avatar.png') }}"
                                            alt="product-image" loading="lazy">
                                    </a>
                                </div>
                                <div class="photo-card-content">
                                    <span>{{ $service->Category->name }}</span>
                                    <h3>{{ $service->name }}</h3>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- photo-banner-sec -->
        @if (isset($themeSetting['info_status']) && $themeSetting['info_status'] == '1')
            <section class="photo-banner-sec pt pb" id="photo-banner"
                style="background-image: url({{ isset($themeSetting['info_image']) ? get_file($themeSetting['info_image']) : asset('packages/workdo/Photography/src/Resources/assets/images/photo-banner.png') }});">
                <div class="container">
                    <div class="photo-banner-content">
                        <h2 class="large-text">
                            {{ isset($themeSetting['info_content']) ? $themeSetting['info_content'] : 'info_content Our photography studio is dedicated to capturing the essence of your' }}
                        </h2>
                        <a href="#appointment" class="btn"
                            tabindex="0">{{ isset($themeSetting['info_button_text']) ? $themeSetting['info_button_text'] : __('Book an Appointment') }}</a>
                    </div>
                </div>
            </section>
        @endif

        <!-- appointment-sec -->
        <section class="appointment-sec pt pb" id="appointment">
            @include('web_layouts.appointment-form')
        </section>

        <!-- our-team-sec -->
        <section class="our-team-sec pt pb" id="our-team">
            <div class="container">
                <div class="flower-vector-img desk-only">
                    <img src="{{ asset('packages/workdo/Photography/src/Resources/assets/images/yellow-vector-flower') }}.png"
                        alt="Vector-flower">
                </div>
                @if (isset($themeSetting['staff_status']) && $themeSetting['staff_status'] == '1')
                    <div class="section-title">
                        <div class="subtitle">
                            {{ isset($themeSetting['staff_title']) ? $themeSetting['staff_title'] : __('OUr TEAM') }}</div>
                        <h2>{{ isset($themeSetting['staff_sub_title']) ? $themeSetting['staff_sub_title'] : __('OUR PHOTOGRAPHY TEAm') }}
                        </h2>
                    </div>
                @endif
                <div class="our-team-slider swiper">
                    <div class="swiper-wrapper">
                        @foreach ($staffs as $staff)
                            <div class="swiper-slide">
                                <div class="our-team-card">
                                    <div class="our-team-img">
                                        <img src="{{ check_file($staff->user->avatar) ? get_file($staff->user->avatar) : get_file('uploads/default/avatar.png') }}"
                                            alt="client-logo-image" loading="lazy">
                                    </div>
                                    <div class="our-team-content text-center">
                                        <div class="team-designation">
                                            <p>{{ $staff->name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- testimonial-sec -->
        <section class="testimonial-sec" id="testimonial">
            <div class="swiper text-slider">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        @if (isset($themeSetting['testimonial_status']) && $themeSetting['testimonial_status'] == '1')
                            <div class="testimonial-title">
                                <h2>{{ isset($themeSetting['testimonial_title']) ? $themeSetting['testimonial_title'] : __('testimonial') }}
                                </h2>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="swiper testimonial-slider">
                    <div class="swiper-wrapper">
                        @foreach ($testimonials as $testimonial)
                            <div class="swiper-slide">
                                <div class="testimonial-card">
                                    <div class="testimonial-inner">
                                        <div class="testimonial-image">
                                            <img src="{{ check_file($testimonial->image) ? get_file($testimonial->image) : get_file('uploads/default/avatar.png') }}"
                                                alt="" loading="lazy" class="main-testimonial-img">
                                        </div>
                                        <div class="testimonial-content">
                                            <h5>
                                                {{ $testimonial->customer->name }}
                                            </h5>
                                            <p>{{ $testimonial->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="arrow-wrapper flex align-center">
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- portfolio-sec -->
        <section class="portfolio-sec pt" id="portfolio">
            @if (isset($themeSetting['portfolio-title_status']) && $themeSetting['portfolio-title_status'] == '1')
                <div class="container">
                    <div class="flower-vector-img desk-only">
                        <img src="{{ asset('packages/workdo/Photography/src/Resources/assets/images/vector-flower.png') }}"
                            alt="Vector-flower">
                    </div>
                    <div class="section-title text-center">
                        <div class="subtitle">
                            {{ isset($themeSetting['portfolio-title_title']) ? $themeSetting['portfolio-title_title'] : __('PHOTOGRAPHY PORTFOLIO') }}
                        </div>
                        <h2>{{ isset($themeSetting['portfolio-title_sub_title']) ? $themeSetting['portfolio-title_sub_title'] : __('Creative photo projects') }}
                        </h2>
                    </div>
                </div>
            @endif
            @if (isset($themeSetting['portfolio_status']) && $themeSetting['portfolio_status'] == '1')
                <div class="portfolio-slider swiper">
                    <div class="swiper-wrapper">
                        @if (count(json_decode($themeSetting['portfolio_repeater'])) > 0)
                            @foreach (json_decode($themeSetting['portfolio_repeater']) as $portfolio_repeater)
                                <div class="swiper-slide">
                                    <div class="portfolio-card">
                                        <div class="portfolio-img">
                                            <img src="{{ isset($portfolio_repeater->image) ? get_file($portfolio_repeater->image) : asset('packages/workdo/Photography/src/Resources/assets/images/portfolio-1.png') }}"
                                                alt="client-logo-image" loading="lazy">
                                        </div>
                                        <div class="portfolio-content text-center">
                                            <div class="pro-year">
                                                <span>{{ isset($portfolio_repeater->small_text) ? $portfolio_repeater->small_text : __('2020') }}
                                                </span>
                                                <p>{{ isset($portfolio_repeater->big_text) ? $portfolio_repeater->big_text : __('beauty') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <!-- client-logo-sec -->

        @if (isset($themeSetting['brand_carousel_status']) && $themeSetting['brand_carousel_status'] == '1')
            <section class="client-logo-sec pt pb">
                <div class="container">
                    <div class="client-logo-slider swiper">
                        <div class="swiper-wrapper">
                            @if (count(json_decode($themeSetting['brand_carousel_repeater'])) > 0)
                                @foreach (json_decode($themeSetting['brand_carousel_repeater']) as $brand_carousel_repeater)
                                    <div class="swiper-slide client-logo-card">
                                        <a href="#" tabindex="0">
                                            <img src="{{ isset($brand_carousel_repeater->image) ? get_file($brand_carousel_repeater->image) : asset('packages/workdo/Photography/src/Resources/assets/images/client-logo-1.png') }}"
                                                alt="client-logo-image" loading="lazy">
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- article-sec -->
        <section class="article-sec pt pb" id="article">
            <div class="container">
                @if (isset($themeSetting['blog_status']) && $themeSetting['blog_status'] == '1')
                    <div class="section-title">
                        <div class="subtitle">
                            {{ isset($themeSetting['blog_title']) ? $themeSetting['blog_title'] : __('OUR article') }}
                        </div>
                        <h2>{{ isset($themeSetting['blog_sub_title']) ? $themeSetting['blog_sub_title'] : __('From our blog') }}
                        </h2>
                    </div>
                @endif
                <div class="article-slider-wrp">
                    <div class="article-slider swiper">
                        <div class="swiper-wrapper">
                            @foreach ($blogs as $blog)
                                <div class="swiper-slide">
                                    <div class="article-card">
                                        <div class="article-card-inner flex align-center">
                                            <div class="article-card-image">
                                                <a href="#" tabindex="0" class="article-image">
                                                    <img src="{{ check_file($blog->image) ? get_file($blog->image) : get_file('uploads/default/avatar.png') }}"
                                                        alt="article-card-image" loading="lazy">
                                                </a>
                                            </div>
                                            <div class="article-content uppercase">
                                                <div class="article-content-top">
                                                    <div class="author-name">
                                                        <p>{{ $blog->title }}</p>
                                                    </div>
                                                    <h6>
                                                        <a href="#" tabindex="0">
                                                            {{ $blog->description }}
                                                        </a>
                                                    </h6>

                                                    <span
                                                        class="comment">{{ \Carbon\Carbon::parse($blog->date)->format('F j, Y') }}</span>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                        <!-- </div> -->
                    </div>
                </div>
            </div>
            <div class="star-vector-img desk-only">
                <img src="{{ asset('packages/workdo/Photography/src/Resources/assets/images/Vector-star.png') }}" alt="Vector-star">
            </div>
            <div class="arrow-vector-img desk-only">
                <img src="{{ asset('packages/workdo/Photography/src/Resources/assets/images/Arrow.png') }}" alt="Arrow">
            </div>
        </section>
        <!-- contact-us -->
        <section class="contact-us pt" id="contact-us">
            <div class="container">
                <div class="row contact-us-wrapper">
                    <div class="col-md-7 col-12">
                        <div class="contact-left-inner">
                            <form class="contact-form" action="{{ route('contacts.store', ['business' => $business]) }}"
                                method="post">
                                @csrf
                                <div class="form-container">
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control" placeholder="Name"
                                                    required="" name="name" id="name">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <input type="email" class="form-control" placeholder="Email"
                                                    required="" name="email" id="email">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <input type="number" class="form-control" placeholder="Phone Number"
                                                    required="" name="contact" id="contact">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control" placeholder="Subject"
                                                    required="" name="subject" id="subject">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <textarea class="form-control" name="message" placeholder="Message" rows="8"
                                                    id="description" required=""></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="theme" value="{{ $module }}">
                                <div class="form-container">
                                    <button class="btn contact-btn" type="submit">
                                        {{ __('Send') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if (isset($themeSetting['contact_info_status']) && $themeSetting['contact_info_status'] == '1')
                        <div class="col-md-5 col-12">
                            <div class="contact-right-inner">
                                <div class="contact-right-top">
                                    <div class="section-title">
                                        <div class="subtitle">
                                            {{ isset($themeSetting['contact_info_title']) ? $themeSetting['contact_info_title'] : __('photo studio') }}
                                        </div>
                                        <h2>{{ isset($themeSetting['contact_info_sub_title']) ? $themeSetting['contact_info_sub_title'] : __('contact') }}
                                        </h2>
                                    </div>
                                    <p>{{ isset($themeSetting['contact_info_description']) ? $themeSetting['contact_info_description'] : __('Got questions or ready to book your session? Reach out to us! Were here to help bring your vision to life. Contact us today to get started.') }}
                                    </p>
                                </div>
                                <div class="contact-right-bottom">
                                    <ul>
                                        <li class="contact-info">
                                            <p><span>{{ isset($themeSetting['contact_info_label_1']) ? $themeSetting['contact_info_label_1'] : __('Address:') }}
                                                </span>{{ isset($themeSetting['contact_info_value_1']) ? $themeSetting['contact_info_value_1'] : __('7515 Carriage Court, Coachella, CA, 92236 USA') }}
                                            </p>
                                        </li>
                                        <li class="contact-info">
                                            <p><span>{{ isset($themeSetting['contact_info_label_2']) ? $themeSetting['contact_info_label_2'] : __('Phone:') }}</span>
                                                <a href="tel:{{ isset($themeSetting['contact_info_value_2']) ? $themeSetting['contact_info_value_2'] : __('(+001) 123-456-7890') }}"
                                                    tabindex="0">{{ isset($themeSetting['contact_info_value_2']) ? $themeSetting['contact_info_value_2'] : __('(+001) 123-456-7890') }}
                                                </a>
                                            </p>
                                        </li>
                                        <li class="contact-info">
                                            <p><span>{{ isset($themeSetting['contact_info_label_3']) ? $themeSetting['contact_info_label_3'] : __('mail:') }}</span>
                                                <a href="mailto: {{ isset($themeSetting['contact_info_value_3']) ? $themeSetting['contact_info_value_3'] : __('photobooth@templatetrip.com') }}"
                                                    tabindex="0">{{ isset($themeSetting['contact_info_value_3']) ? $themeSetting['contact_info_value_3'] : __('photobooth@templatetrip.com') }}</a>
                                            </p>
                                        </li>
                                    </ul>
                                </div>
                                <div class="vector-img desk-only">
                                    <img src="{{ asset('packages/workdo/Photography/src/Resources/assets/images/contact-vector.png') }}"
                                        alt="" loading="lazy">
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </section>

        <!-- mapping-sec -->
        @if (isset($themeSetting['map_area_status']) && $themeSetting['map_area_status'] == '1')
            <section class="contact-direction-sec">
                <div class="container">
                    <div class="contact-direction-inner">
                        @isset($themeSetting['map_area_iframe'])
                            {!! $themeSetting['map_area_iframe'] !!}
                        @endisset
                    </div>
                </div>
            </section>
        @endif

        <!-- gallery-sec -->
        <section class="gallery-sec">
            @if (isset($themeSetting['gallery-title_status']) && $themeSetting['gallery-title_status'] == '1')
                <div class="gallery-title">
                    <h2>{{ isset($themeSetting['gallery-title_title']) ? $themeSetting['gallery-title_title'] : __('OUR GELEERY') }}
                    </h2>
                </div>
            @endif
            @if (isset($themeSetting['gallery_carousel_status']) && $themeSetting['gallery_carousel_status'] == '1')
                <div class="swiper gallery-slider">
                    <div class="swiper-wrapper">
                        @if (count(json_decode($themeSetting['gallery_carousel_repeater'])) > 0)
                            @foreach (json_decode($themeSetting['gallery_carousel_repeater']) as $gallery_carousel_repeater)
                                <div class="swiper-slide">
                                    <div class="gallery-image">
                                        <img src="{{ isset($gallery_carousel_repeater->image) ? get_file($gallery_carousel_repeater->image) : asset('packages/workdo/Photography/src/Resources/assets/images/gallery-1.png') }}"
                                            alt="gallery-image" loading="lazy">
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            @endif
        </section>
    </main>

    <!-- footer start here -->
    <footer class="site-footer footer-style-seven">
        <div class="container">
            <div class="footer-wrapper flex justify-between">
                <div class="footer-left">
                    @if (isset($themeSetting['footer_top_status']) && $themeSetting['footer_top_status'] == '1')
                        <div class="footer-logo">
                            <a href="#" tabindex="0">
                                <img src="{{ isset($themeSetting['footer_top_image']) ? get_file($themeSetting['footer_top_image']) : asset('packages/workdo/Photography/src/Resources/assets/images/footer-logo.png') }}"
                                    alt="logo" loading="lazy">
                            </a>
                        </div>
                    @endif
                    @if (isset($themeSetting['news_letter_status']) && $themeSetting['news_letter_status'] == '1')
                        <form class="subscribe-form-wrapper"
                            action="{{ route('subscribes.store', ['business' => $business]) }}" method="post">
                            @csrf
                            <div class="input-wrapper flex">
                                <input type="email" placeholder="Your Email Address" class="form-control"
                                    name="email" id="email" required>
                                <button type="submit"
                                    class="subscribe-btn btn">{{ isset($themeSetting['news_letter_button_text']) ? $themeSetting['news_letter_button_text'] : __('Subscribe') }}
                                </button>
                            </div>
                            <input type="hidden" name="theme" value="{{ $module }}">
                        </form>
                        <p>{{ isset($themeSetting['news_letter_sub_title']) ? $themeSetting['news_letter_sub_title'] : __('We care about our customers - you have always been an integral part of who we are. Join today.') }}
                        </p>
                    @endif
                </div>
                <div class="footer-right">
                    <div class="footer-row flex">
                        @if (isset($themeSetting['footer_top_status']) && $themeSetting['footer_top_status'] == '1')
                            <div class="footer-col">
                                <div class="footer-widget set has-children1">
                                    <h2 class="acnav-label1">
                                        <span>
                                            {{ isset($themeSetting['footer_top_title_1']) ? $themeSetting['footer_top_title_1'] : __('Quick link') }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 10 5"
                                            width="10" height="5">
                                            <path class="a"
                                                d="m5.4 5.1q-0.3 0-0.5-0.2l-3.7-3.7c-0.3-0.3-0.3-0.7 0-1 0.2-0.3 0.7-0.3 0.9 0l3.3 3.2 3.2-3.2c0.2-0.3 0.7-0.3 0.9 0 0.3 0.3 0.3 0.7 0 1l-3.7 3.7q-0.2 0.2-0.4 0.2z">
                                            </path>
                                        </svg>
                                    </h2>
                                    <ul class="acnav-list1">
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_1_label_1']) ? $themeSetting['footer_top_1_label_1'] : __('Home') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_1_label_2']) ? $themeSetting['footer_top_1_label_2'] : __('portfolio') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_1_label_3']) ? $themeSetting['footer_top_1_label_3'] : __('our team') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_1_label_4']) ? $themeSetting['footer_top_1_label_4'] : __('article') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_1_label_5']) ? $themeSetting['footer_top_1_label_5'] : __('Appointment') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="footer-col">
                                <div class="footer-widget set has-children1">
                                    <h2 class="acnav-label1">
                                        <span>{{ isset($themeSetting['footer_top_title_2']) ? $themeSetting['footer_top_title_2'] : __('Our Company') }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 10 5"
                                            width="10" height="5">
                                            <path class="a"
                                                d="m5.4 5.1q-0.3 0-0.5-0.2l-3.7-3.7c-0.3-0.3-0.3-0.7 0-1 0.2-0.3 0.7-0.3 0.9 0l3.3 3.2 3.2-3.2c0.2-0.3 0.7-0.3 0.9 0 0.3 0.3 0.3 0.7 0 1l-3.7 3.7q-0.2 0.2-0.4 0.2z">
                                            </path>
                                        </svg>
                                    </h2>
                                    <ul class="acnav-list1">
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_2_label_1']) ? $themeSetting['footer_top_2_label_1'] : __('About Us') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_2_label_2']) ? $themeSetting['footer_top_2_label_2'] : __('Our Services') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_2_label_3']) ? $themeSetting['footer_top_2_label_3'] : __('Contact Us') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" tabindex="0">
                                                {{ isset($themeSetting['footer_top_2_label_4']) ? $themeSetting['footer_top_2_label_4'] : __('Pricing') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="footer-col">
                                <div class="footer-widget set has-children1">
                                    <h2 class="acnav-label1">
                                        <span>
                                            {{ isset($themeSetting['footer_top_title_3']) ? $themeSetting['footer_top_title_3'] : __('Contact Info') }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 10 5"
                                            width="10" height="5">
                                            <path class="a"
                                                d="m5.4 5.1q-0.3 0-0.5-0.2l-3.7-3.7c-0.3-0.3-0.3-0.7 0-1 0.2-0.3 0.7-0.3 0.9 0l3.3 3.2 3.2-3.2c0.2-0.3 0.7-0.3 0.9 0 0.3 0.3 0.3 0.7 0 1l-3.7 3.7q-0.2 0.2-0.4 0.2z">
                                            </path>
                                        </svg>
                                    </h2>
                                    <ul class="acnav-list1">
                                        <li class="footer-col-links">
                                            <div class="contact-info">
                                                <p><span>{{ isset($themeSetting['contact_info_label_1']) ? $themeSetting['contact_info_label_1'] : __('Address:') }}
                                                    </span>{{ isset($themeSetting['contact_info_value_1']) ? $themeSetting['contact_info_value_1'] : __('7515 Carriage Court, Coachella, CA, 92236 USA') }}
                                                </p>
                                            </div>
                                        </li>
                                        <li class="footer-col-links">
                                            <div class="contact-info">
                                                <p><span>{{ isset($themeSetting['contact_info_label_2']) ? $themeSetting['contact_info_label_2'] : __('Phone:') }}</span>
                                                    <a href="tel:{{ isset($themeSetting['contact_info_value_2']) ? $themeSetting['contact_info_value_2'] : __('(+001) 123-456-7890') }}"
                                                        tabindex="0">{{ isset($themeSetting['contact_info_value_2']) ? $themeSetting['contact_info_value_2'] : __('(+001) 123-456-7890') }}</a>
                                                </p>
                                            </div>
                                        </li>
                                        <li class="footer-col-links">
                                            <div class="contact-info">
                                                <p><span>{{ isset($themeSetting['contact_info_label_3']) ? $themeSetting['contact_info_label_3'] : __('mail:') }}</span>
                                                    <a href="mailto:{{ isset($themeSetting['contact_info_value_3']) ? $themeSetting['contact_info_value_3'] : __('photobooth@templatetrip.com') }}"
                                                        tabindex="0">{{ isset($themeSetting['contact_info_value_3']) ? $themeSetting['contact_info_value_3'] : __('photobooth@templatetrip.com') }}</a>
                                                </p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        @if (isset($themeSetting['footer_bottom_status']) && $themeSetting['footer_bottom_status'] == '1')
            <div class="footer-bottom">
                <div class="container">
                    <div class="footer-bottom-inner flex align-center justify-between">
                        <p>{{ isset($themeSetting['footer_bottom_copyright_text']) ? $themeSetting['footer_bottom_copyright_text'] : __('Copyright 2024, All Rights Reserved.') }}
                        </p>
                        <ul class="footer-social-icon flex align-center">
                            <li>
                                <a href="{{ isset($themeSetting['footer_bottom_social_icon_1_title']) ? $themeSetting['footer_bottom_social_icon_1_title'] : __('https://www.facebook.com/facebook/') }}"
                                    tabindex="0">
                                    <i
                                        class="{{ isset($themeSetting['footer_bottom_social_icon_1']) ? $themeSetting['footer_bottom_social_icon_1'] : __('fab fa-whatsapp') }}"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ isset($themeSetting['footer_bottom_social_icon_2_title']) ? $themeSetting['footer_bottom_social_icon_2_title'] : __('https://www.instagram.com/') }}"
                                    target="_blank">
                                    <i
                                        class="{{ isset($themeSetting['footer_bottom_social_icon_2']) ? $themeSetting['footer_bottom_social_icon_2'] : __('fab fa-facebook') }}"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ isset($themeSetting['footer_bottom_social_icon_3_title']) ? $themeSetting['footer_bottom_social_icon_3_title'] : __('https://www.youtube.com/') }}"
                                    target="_blank">
                                    <i
                                        class="{{ isset($themeSetting['footer_bottom_social_icon_3']) ? $themeSetting['footer_bottom_social_icon_3'] : __('fa-brands fa-x-twitter') }}"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </footer>
    <!-- footer start here -->

@endsection

<script>
    function myFunction(x) {
        x.classList.toggle("change");
    }
</script>

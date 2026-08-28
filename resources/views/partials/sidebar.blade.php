<nav class="dash-sidebar light-sidebar {{ empty($company_settings['site_transparent']) || $company_settings['site_transparent'] == 'on' ? 'transprent-bg' : '' }}">
    <div class="navbar-wrapper">
        <div class="m-header main-logo">
            <a href="{{ route('home') }}" class="b-brand">
                <!-- ========   change your logo hear   ============ -->
                <img src="{{ get_file(sidebar_logo()) }}{{ '?' . time() }}" alt="" class="logo logo-lg" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="dash-navbar">
                {!! getMenu() !!}
                @stack('custom_side_menu')
            </ul>
        </div>
    </div>
</nav>

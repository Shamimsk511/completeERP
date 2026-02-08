@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand-md') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    <div class="{{ config('adminlte.classes_topnav_container', 'container') }}">

        {{-- Navbar brand logo --}}
        @if(config('adminlte.logo_img_xl'))
            @include('adminlte::partials.common.brand-logo-xl')
        @else
            @include('adminlte::partials.common.brand-logo-xs')
        @endif

        {{-- Navbar toggler button --}}
        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Navbar collapsible menu --}}
        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            {{-- Navbar left links --}}
            <ul class="nav navbar-nav">
                {{-- Configured left links --}}
                @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

                {{-- Custom left links --}}
                @yield('content_top_nav_left')
            </ul>
        </div>

        <div class="order-2 d-none d-md-flex flex-grow-1 justify-content-center">
            @include('adminlte::partials.navbar.global-search')
        </div>

        {{-- Navbar right links --}}
        <ul class="navbar-nav ml-auto order-1 order-md-3 navbar-no-expand">
            {{-- Custom right links --}}
            @yield('content_top_nav_right')

            {{-- Configured right links --}}
            @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

            {{-- User menu link --}}
            @if(Auth::user())
                @if(config('adminlte.usermenu_enabled'))
                    @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
                @else
                    @include('adminlte::partials.navbar.menu-item-logout-link')
                @endif
            @endif

            {{-- Chat notifications (right of user menu) --}}
            @can('chat-access')
                <x-adminlte-navbar-notification
                    id="chat-navbar-notification"
                    :href="route('chat.index')"
                    icon="fas fa-comments"
                    :update-cfg="['url' => route('chat.notifications.navbar'), 'period' => 10]"
                    :enable-dropdown-mode="true"
                    dropdown-footer-label="Open Chat"
                />
            @endcan

            {{-- Right sidebar toggler link --}}
            @if($layoutHelper->isRightSidebarEnabled())
                @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
            @endif
        </ul>

    </div>

</nav>

@once
@push('js')
<script>
    $(document).on('click', '.js-chat-mark-read', function(e) {
        e.preventDefault();
        $.post(`{{ route('chat.notifications.read') }}`, {_token: "{{ csrf_token() }}"})
            .done(function() {
                if (typeof _AdminLTE_NavbarNotification !== 'undefined') {
                    const nLink = new _AdminLTE_NavbarNotification('chat-navbar-notification');
                    $.get(`{{ route('chat.notifications.navbar') }}`).done(function(data) {
                        nLink.update(data);
                    });
                }
            });
    });

    $(document).on('click', '.js-chat-notification', function(e) {
        const url = $(this).attr('href');
        const readUrl = $(this).data('read-url');

        if (!readUrl) {
            return;
        }

        e.preventDefault();
        $.post(readUrl, {_token: "{{ csrf_token() }}"})
            .always(function() {
                if (typeof _AdminLTE_NavbarNotification !== 'undefined') {
                    const nLink = new _AdminLTE_NavbarNotification('chat-navbar-notification');
                    $.get(`{{ route('chat.notifications.navbar') }}`).done(function(data) {
                        nLink.update(data);
                        window.location.href = url;
                    }).fail(function() {
                        window.location.href = url;
                    });
                } else {
                    window.location.href = url;
                }
            });
    });
</script>
@endpush
@endonce

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    @include('adminlte::partials.navbar.global-search')

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Chat notifications --}}
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

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif

        {{-- User menu link (keep as last icon) --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif
    </ul>

</nav>

@once
<style>
    .sidebar-edge-toggle {
        position: fixed;
        top: 66px;
        left: calc(250px + 8px);
        width: 30px;
        height: 30px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 999px;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.48), rgba(15, 23, 42, 0.26));
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(2, 6, 23, 0.25);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 1030;
        cursor: pointer;
        transition: left 0.24s ease, background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .sidebar-edge-toggle:hover {
        border-color: rgba(255, 255, 255, 0.48);
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.34));
        box-shadow: 0 8px 18px rgba(2, 6, 23, 0.32);
    }

    .sidebar-edge-toggle:focus {
        outline: none;
    }

    .sidebar-edge-toggle:focus-visible {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.28), 0 8px 18px rgba(2, 6, 23, 0.32);
    }

    .sidebar-edge-toggle i {
        font-size: 13px;
        line-height: 1;
        transition: transform 0.24s ease;
    }

    body.sidebar-collapse .sidebar-edge-toggle {
        left: calc(4.6rem + 8px);
    }

    body.sidebar-collapse .sidebar-edge-toggle i {
        transform: rotate(180deg);
    }

    .main-header.navbar .navbar-nav.ml-auto {
        align-items: center;
        gap: 2px;
    }

    .main-header.navbar .navbar-nav.ml-auto > .nav-item > .nav-link {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 1199.98px) {
        .main-header.navbar .navbar-nav.ml-auto {
            gap: 0;
        }
    }

    @media (max-width: 991.98px) {
        .sidebar-edge-toggle {
            left: 10px;
            top: 64px;
        }

        body.sidebar-open .sidebar-edge-toggle {
            left: calc(250px + 10px);
        }
    }
</style>

@if(! $layoutHelper->isLayoutTopnavEnabled())
    <button type="button" class="sidebar-edge-toggle" data-widget="pushmenu" data-enable-remember="true" aria-label="Toggle Sidebar">
        <i class="fas fa-angle-left" aria-hidden="true"></i>
    </button>
@endif

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

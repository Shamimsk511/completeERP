@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
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

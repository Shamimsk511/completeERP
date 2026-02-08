@php( $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout') )
@php( $profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout') )

@if (config('adminlte.usermenu_profile_url', false))
    @php( $profile_url = Auth::user()->adminlte_profile_url() )
@endif

@if (config('adminlte.use_route_url', false))
    @php( $profile_url = $profile_url ? route($profile_url) : '' )
    @php( $logout_url = $logout_url ? route($logout_url) : '' )
@else
    @php( $profile_url = $profile_url ? url($profile_url) : '' )
    @php( $logout_url = $logout_url ? url($logout_url) : '' )
@endif

@php( $user_name = Auth::user()->name )
@php( $user_desc = trim((string) (config('adminlte.usermenu_desc') ? Auth::user()->adminlte_desc() : (Auth::user()->email ?? '')) ) )
@php( $user_initial = strtoupper(substr(trim($user_name ?: 'U'), 0, 1)) )
@php( $user_menu_links = $adminlte->menu('navbar-user') )
@php( $show_user_image = config('adminlte.usermenu_image') )

<li class="nav-item dropdown user-menu modern-user-menu">

    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle modern-user-menu-toggle d-flex align-items-center" data-toggle="dropdown">
        @if($show_user_image)
            <img src="{{ Auth::user()->adminlte_image() }}"
                 class="user-image img-circle elevation-2 modern-user-avatar"
                 alt="{{ Auth::user()->name }}">
        @else
            <span class="modern-user-avatar modern-user-avatar-fallback">
                {{ $user_initial }}
            </span>
        @endif
        <span class="modern-user-name @if($show_user_image) d-none d-md-inline @endif">
            {{ $user_name }}
        </span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right modern-user-dropdown">

        {{-- User menu header --}}
        @if(!View::hasSection('usermenu_header') && config('adminlte.usermenu_header'))
            <li class="modern-user-dropdown-header">
                <div class="modern-user-profile">
                @if($show_user_image)
                    <img src="{{ Auth::user()->adminlte_image() }}"
                         class="img-circle elevation-2 modern-user-avatar"
                         alt="{{ Auth::user()->name }}">
                @else
                    <span class="modern-user-avatar modern-user-avatar-fallback">
                        {{ $user_initial }}
                    </span>
                @endif
                    <div class="modern-user-meta">
                        <div class="modern-user-title">{{ $user_name }}</div>
                        @if($user_desc !== '')
                            <div class="modern-user-subtitle">{{ $user_desc }}</div>
                        @endif
                    </div>
                </div>
            </li>
        @else
            @yield('usermenu_header')
        @endif

        @if(!empty($user_menu_links))
            <li>
                <hr class="modern-user-divider">
            </li>
            {{-- Configured user menu links --}}
            @each('adminlte::partials.navbar.dropdown-item', $user_menu_links, 'item')
        @endif

        {{-- User menu body --}}
        @hasSection('usermenu_body')
            <li class="user-body modern-user-body">
                @yield('usermenu_body')
            </li>
        @endif

        <li>
            <hr class="modern-user-divider">
        </li>

        {{-- User menu footer --}}
        <li class="user-footer modern-user-footer">
            <div class="modern-user-actions">
                @if($profile_url)
                    <a href="{{ $profile_url }}" class="btn modern-user-btn modern-user-btn-profile">
                        <i class="fa fa-fw fa-user-circle"></i>
                        {{ __('adminlte::menu.profile') }}
                    </a>
                @endif
                <a class="btn modern-user-btn modern-user-btn-logout @if(!$profile_url) w-100 @endif"
                   href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa fa-fw fa-power-off"></i>
                    {{ __('adminlte::adminlte.log_out') }}
                </a>
                <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                    @if(config('adminlte.logout_method'))
                        {{ method_field(config('adminlte.logout_method')) }}
                    @endif
                    {{ csrf_field() }}
                </form>
            </div>
        </li>

    </ul>

</li>

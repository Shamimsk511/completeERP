<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

</aside>
<style>
    /* Modern Sidebar Styling */
.main-sidebar {
    background: linear-gradient(180deg, var(--app-primary, #667eea) 0%, var(--app-accent, #764ba2) 100%) !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Sidebar Brand */
.brand-link {
    background: var(--app-topbar, var(--app-primary, #667eea)) !important;
    background-image: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6))) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: var(--app-sidebar-text, #ffffff) !important;
}

.brand-link:hover {
    background: var(--app-topbar, var(--app-primary, #667eea)) !important;
    background-image: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6))) !important;
    color: white !important;
}

/* Sidebar Menu */
.sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    border-radius: 0.375rem;
    margin: 0.125rem 0.5rem;
    transition: all 0.3s ease;
}

.sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
    transform: translateX(5px);
}

.sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%) !important;
    color: white !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Sidebar Headers */
.sidebar-dark-primary .nav-header {
    color: rgba(255, 255, 255, 0.7) !important;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 1rem;
    padding: 0.5rem 1rem;
}

/* Submenu Styling */
.sidebar-dark-primary .nav-treeview > .nav-item > .nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    padding-left: 2.5rem;
    font-size: 0.875rem;
}

.sidebar-dark-primary .nav-treeview > .nav-item > .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.05) !important;
    color: white !important;
}

.sidebar-dark-primary .nav-treeview > .nav-item > .nav-link.active {
    background-color: rgba(255, 255, 255, 0.15) !important;
    color: white !important;
}

/* Menu Icons */
.nav-icon {
    margin-right: 0.5rem;
    width: 1.2rem;
    text-align: center;
}

/* Sidebar Toggle Button */
.nav-link[data-widget="pushmenu"] {
    color: white !important;
}

.nav-link[data-widget="pushmenu"]:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
}

/* User Panel */
.user-panel {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
    padding: 1rem;
}

.user-panel .info a {
    color: white !important;
    font-weight: 500;
}

/* Scrollbar for sidebar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
/* Modern Navbar with Gradient */
.main-header.navbar {
    background: var(--app-topbar, var(--app-primary, #667eea)) !important;
    background-image: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6))) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
.main-header .navbar-brand {
    background: transparent !important;
}


/* Navbar brand/logo area */
.navbar-brand {
    background: transparent !important;
    color: #fff !important;
}

.navbar-brand:hover {
    color: rgba(255, 255, 255, 0.9) !important;
}

/* Navbar navigation links */
.navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: white !important;
    background-color: rgba(255, 255, 255, 0.1) !important;
    border-radius: 0.375rem;
}

/* Navbar toggler (hamburger menu) */
.navbar-toggler {
    border-color: rgba(255, 255, 255, 0.3) !important;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
}

/* User dropdown in navbar */
.navbar .dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Search form in navbar if you have one */
.navbar .form-control {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
}

.navbar .form-control::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.navbar .form-control:focus {
    background-color: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

/* Navbar text elements */
.navbar-text {
    color: rgba(255, 255, 255, 0.9) !important;
}

/* Active nav items */
.navbar-nav .nav-item.active .nav-link {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    border-radius: 0.375rem;
}

/* Dropdown items in navbar */
.navbar .dropdown-item {
    color: #333;
    transition: all 0.3s ease;
}

.navbar .dropdown-item:hover {
    background: linear-gradient(135deg, var(--app-primary, #667eea) 0%, var(--app-accent, #764ba2) 100%);
    color: white;
}

.custom-brand-gradient {
    background: var(--app-topbar, var(--app-primary, #667eea)) !important;
    background-image: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6))) !important;
    color: white !important;
}
/* Fix the white brand area in top-left corner */
.main-sidebar .brand-link {
    background: var(--app-topbar, var(--app-primary, #667eea)) !important;
    background-image: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6))) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
}

/* Ensure brand text is white */
.brand-link .brand-text {
    color: white !important;
    font-weight: 600;
}

/* If you have a logo image, ensure it's visible */
.brand-link .brand-image {
    opacity: 1 !important;
}

/* Remove any conflicting background from navbar brand area */
.main-header .navbar .navbar-brand {
    background: transparent !important;
}

/* Additional fix for the specific brand container */
.sidebar-dark-primary .brand-link {
    background: var(--app-topbar, var(--app-primary, #667eea)) !important;
    background-image: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6))) !important;
    color: white !important;
}

.sidebar-dark-primary .brand-link:hover {
    background: var(--app-topbar, var(--app-primary, #667eea)) !important;
    background-image: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6))) !important;
    opacity: 0.95;
}

/* Final override: seamless app title strip + topbar */
.main-header.navbar,
.main-header.navbar.navbar-dark,
.main-header.navbar.navbar-dark.navbar-primary,
.main-sidebar .brand-link,
.sidebar-dark-primary .brand-link {
    background: var(--app-topbar, var(--app-primary, #667eea)) !important;
    background-image: none !important;
}

.main-header.navbar,
.main-header.navbar.navbar-dark,
.main-header.navbar.navbar-dark.navbar-primary {
    border-bottom: 0 !important;
    box-shadow: none !important;
    position: sticky;
    top: 0;
    z-index: 1055;
}

.brand-link,
.main-sidebar .brand-link,
.sidebar-dark-primary .brand-link {
    border-bottom: 0 !important;
    box-shadow: none !important;
    position: relative;
    overflow: visible !important;
}

.main-sidebar .brand-link {
    min-height: 56px;
    height: auto !important;
    display: flex;
    align-items: center;
    padding-top: 7px;
    padding-bottom: 7px;
}

.main-sidebar .brand-link .brand-text {
    display: block;
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
    line-height: 1.15;
    font-size: 0.84rem;
    font-weight: 700 !important;
    padding-right: 6px;
}

.main-sidebar,
.sidebar-dark-primary {
    border-right: 0 !important;
}

.main-header.navbar::after,
.brand-link::after,
.main-sidebar .brand-link::after {
    content: none !important;
    display: none !important;
}

</style>

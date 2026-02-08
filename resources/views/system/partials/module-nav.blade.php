<div class="system-module-nav mb-4">
    <div class="nav nav-pills system-nav-pills">
        <a class="nav-link {{ request()->routeIs('system.index') ? 'active' : '' }}"
           href="{{ route('system.index') }}">
            <i class="fas fa-server mr-1"></i> Overview
        </a>
        <a class="nav-link {{ request()->routeIs('system.backups') ? 'active' : '' }}"
           href="{{ route('system.backups') }}">
            <i class="fas fa-database mr-1"></i> Backups
        </a>
        <a class="nav-link {{ request()->routeIs('system.update') ? 'active' : '' }}"
           href="{{ route('system.update') }}">
            <i class="fas fa-sync-alt mr-1"></i> Updates
        </a>
    </div>
</div>

@extends('layouts.modern-admin')

@section('title', 'System Management')
@section('page_title', 'System Management')

@section('header_actions')
    <div class="header-actions-group">
        <div class="system-status-indicator">
            <span class="status-dot status-online"></span>
            <span class="status-text">System Online</span>
        </div>
    </div>
@stop

@section('additional_css')
<style>
    .header-actions-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .system-status-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.08);
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .status-online {
        background: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
    }
    .system-intro .badge {
        margin-left: 6px;
    }
    .system-tab-nav .nav-link {
        font-weight: 600;
        color: var(--app-primary-dark, #1f2937);
        border-radius: 10px;
    }
    .system-tab-nav .nav-link.active {
        background: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6)));
        color: #fff;
    }
    .mini-stat {
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 12px;
        background: rgba(255, 255, 255, 0.9);
    }
    .mini-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--app-muted, #6b7280);
    }
    .mini-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--app-primary-dark, #1f2937);
        margin-top: 2px;
    }
    .mini-meta {
        font-size: 12px;
        color: var(--app-muted, #6b7280);
        margin-top: 2px;
    }
    .system-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    .system-action:last-child {
        border-bottom: 0;
    }
    .action-title {
        font-weight: 600;
        color: var(--app-text, #0f172a);
    }
    .action-desc {
        font-size: 12px;
        color: var(--app-muted, #6b7280);
        margin-top: 2px;
    }
    .action-desc code {
        background: rgba(15, 23, 42, 0.06);
        padding: 2px 6px;
        border-radius: 6px;
    }
    .debug-toggle-wrap {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .debug-status {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.08);
        color: var(--app-primary-dark, #1f2937);
        min-width: 42px;
        text-align: center;
    }
    .debug-status.is-on {
        background: rgba(34, 197, 94, 0.15);
        color: #166534;
    }
    .debug-status.is-off {
        background: rgba(239, 68, 68, 0.12);
        color: #7f1d1d;
    }
    .debug-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .debug-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .debug-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: rgba(15, 23, 42, 0.2);
        border-radius: 999px;
        transition: all 0.2s ease;
    }
    .debug-slider::before {
        content: "";
        position: absolute;
        height: 18px;
        width: 18px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.2s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }
    .debug-switch input:checked + .debug-slider {
        background: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6)));
    }
    .debug-switch input:checked + .debug-slider::before {
        transform: translateX(20px);
    }
    @media (max-width: 767.98px) {
        .system-action {
            flex-direction: column;
            align-items: flex-start;
        }
        .system-action .btn {
            width: 100%;
        }
    }
</style>
@stop

@section('additional_js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const backupType = document.getElementById('backup-type');
        const backupForm = document.getElementById('backup-create-form');
        const successMessage = @json(session('success'));
        const errorMessage = @json(session('error'));

        if (backupForm && backupType) {
            backupForm.addEventListener('submit', function () {
                if (backupType.value === 'full') {
                    backupForm.setAttribute('action', '{{ route('system.create-full-backup') }}');
                } else {
                    backupForm.setAttribute('action', '{{ route('system.create-backup') }}');
                }
            });
        }

        function showAlert(type, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'error'),
                    title: type === 'success' ? 'Success' : (type === 'warning' ? 'Warning' : 'Error'),
                    text: message
                });
            } else {
                alert(message);
            }
        }

        if (successMessage) {
            showAlert('success', successMessage);
        }
        if (errorMessage) {
            showAlert('danger', errorMessage);
        }


        function setLoading(button, isLoading) {
            if (!button) return;
            button.disabled = isLoading;
            if (isLoading) {
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Working';
            } else if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
            }
        }

        function handleAction(btn) {
            const url = btn.dataset.url;
            const method = (btn.dataset.method || 'POST').toUpperCase();
            const payload = btn.dataset.payload ? JSON.parse(btn.dataset.payload) : null;
            const confirmText = btn.dataset.confirm;

            if (confirmText && !confirm(confirmText)) {
                return;
            }

            setLoading(btn, true);

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: method === 'GET' ? null : JSON.stringify(payload || {})
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    if (data.table_count !== undefined) {
                        if (data.critical_tables_ok) {
                            showAlert('success', 'Database OK. Tables: ' + data.table_count);
                        } else {
                            showAlert('warning', 'Database connected, missing tables: ' + (data.missing_tables || []).join(', '));
                        }
                    } else {
                        showAlert('success', data.message || 'Action completed.');
                    }

                    if (data.status && data.status.pending_count !== undefined) {
                        const pendingCountEl = document.getElementById('pending-count');
                        const pendingCountCard = document.getElementById('pending-count-card');
                        if (pendingCountEl) pendingCountEl.textContent = data.status.pending_count;
                        if (pendingCountCard) pendingCountCard.textContent = data.status.pending_count;
                    }

                } else {
                    showAlert('danger', data.message || 'Action failed.');
                }
            })
            .catch(function () {
                showAlert('danger', 'Request failed. Please try again.');
            })
            .finally(function () {
                setLoading(btn, false);
            });
        }

        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.js-action');
            if (!btn) return;
            event.preventDefault();
            handleAction(btn);
        });


        document.querySelectorAll('.js-run-migration').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const migration = btn.dataset.migration;
                const row = btn.closest('tr');

                setLoading(btn, true);

                fetch('{{ route('system.update.migrate-single') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ migration: migration })
                })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        showAlert('success', data.message || 'Migration completed.');
                        if (row) row.remove();
                        const pendingCountEl = document.getElementById('pending-count');
                        const pendingCountCard = document.getElementById('pending-count-card');
                        if (pendingCountEl) pendingCountEl.textContent = Math.max(0, parseInt(pendingCountEl.textContent || '0', 10) - 1);
                        if (pendingCountCard) pendingCountCard.textContent = pendingCountEl ? pendingCountEl.textContent : pendingCountCard.textContent;
                        const table = document.getElementById('pending-migrations-table');
                        if (table && table.querySelectorAll('tbody tr').length === 0) {
                            table.parentElement.innerHTML = '<div class="p-4 text-center text-muted">No pending migrations.</div>';
                        }
                    } else {
                        showAlert('danger', data.message || 'Migration failed.');
                    }
                })
                .catch(function () {
                    showAlert('danger', 'Migration request failed.');
                })
                .finally(function () {
                    setLoading(btn, false);
                });
            });
        });

        const debugToggle = document.getElementById('debugToggle');
        const debugStatus = document.getElementById('debugStatus');
        if (debugToggle) {
            debugToggle.addEventListener('change', function () {
                const enable = debugToggle.checked;
                if (enable && !confirm('Enable debug mode? This can expose sensitive data.')) {
                    debugToggle.checked = false;
                    return;
                }

                debugToggle.disabled = true;

                fetch('{{ \Illuminate\Support\Facades\Route::has('system.update.toggle-debug') ? route('system.update.toggle-debug') : url('/system/update/toggle-debug') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ debug: enable })
                })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        const isOn = !!data.debug;
                        if (debugStatus) {
                            debugStatus.textContent = isOn ? 'On' : 'Off';
                            debugStatus.classList.toggle('is-on', isOn);
                            debugStatus.classList.toggle('is-off', !isOn);
                        }
                        debugToggle.checked = isOn;
                        showAlert('success', data.message || 'Debug mode updated.');
                    } else {
                        debugToggle.checked = !enable;
                        showAlert('danger', data.message || 'Failed to update debug mode.');
                    }
                })
                .catch(function () {
                    debugToggle.checked = !enable;
                    showAlert('danger', 'Request failed. Please try again.');
                })
                .finally(function () {
                    debugToggle.disabled = false;
                });
            });
        }

        const cleanupBtn = document.querySelector('.js-cleanup-backups');
        if (cleanupBtn) {
            cleanupBtn.addEventListener('click', function () {
                if (!confirm('Delete old backups to free disk space?')) {
                    return;
                }
                setLoading(cleanupBtn, true);
                fetch(cleanupBtn.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({})
                })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        showAlert('success', data.message || 'Cleanup completed.');
                        setTimeout(function () { window.location.reload(); }, 900);
                    } else {
                        showAlert('danger', data.message || 'Cleanup failed.');
                    }
                })
                .catch(function () {
                    showAlert('danger', 'Cleanup request failed.');
                })
                .finally(function () {
                    setLoading(cleanupBtn, false);
                });
            });
        }
    });
</script>
@stop

@section('page_content')

    <div class="system-intro card modern-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1">No terminal required</h6>
                    <p class="text-muted mb-0">All actions below run server-side tools safely for shared hosting. Each button shows the command or action it performs.</p>
                </div>
                <div class="system-tags">
                    <span class="badge badge-light">Shared Hosting Ready</span>
                    <span class="badge badge-light">Safe Actions</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card system-tabs-card mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills system-tab-nav" id="systemTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-overview-link" data-toggle="pill" href="#tab-overview" role="tab">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-backups-link" data-toggle="pill" href="#tab-backups" role="tab">Backups</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-updates-link" data-toggle="pill" href="#tab-updates" role="tab">Updates</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="systemTabContent">
        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
            @php
                $usagePercent = $diskSpace['total'] > 0 ? (($diskSpace['total'] - $diskSpace['free']) / $diskSpace['total']) * 100 : 0;
                $cacheTotalFiles = $cacheInfo['view_cache_files'] + $cacheInfo['cache_files'];
            @endphp
            <div class="row">
                <div class="col-lg-4">
                    <div class="card modern-card mb-3">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Snapshot</h3>
                        </div>
                        <div class="card-body">
                            <div class="mini-stat">
                                <div class="mini-label">Disk Usage</div>
                                <div class="mini-value">{{ number_format($usagePercent, 1) }}%</div>
                                <div class="mini-meta">{{ $diskSpace['used_formatted'] }} / {{ $diskSpace['total_formatted'] }}</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-label">Cache Files</div>
                                <div class="mini-value">{{ $cacheTotalFiles }}</div>
                                <div class="mini-meta">Views {{ $cacheInfo['view_cache_files'] }}, App {{ $cacheInfo['cache_files'] }}</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-label">Backups</div>
                                <div class="mini-value" id="backup-count">{{ count($backups) }}</div>
                                <div class="mini-meta">Files in storage/app/backups</div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-label">Pending Migrations</div>
                                <div class="mini-value" id="pending-count">{{ $migrationStatus['pending_count'] }}</div>
                                <div class="mini-meta">Total {{ $migrationStatus['total'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card modern-card mb-3">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-tools"></i> Quick Maintenance</h3>
                        </div>
                        <div class="card-body">
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Clear All Caches</div>
                                    <div class="action-desc">Runs <code>cache:clear</code>, <code>config:clear</code>, <code>route:clear</code>, <code>view:clear</code>.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-warning js-action" data-method="POST" data-url="{{ route('system.cache.clear') }}" data-payload='{"cache_type":"all"}'>Run</button>
                            </div>
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Optimize Application</div>
                                    <div class="action-desc">Runs <code>config:cache</code>, <code>route:cache</code>, <code>view:cache</code>.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-primary js-action" data-method="POST" data-url="{{ route('system.cache.optimize') }}" data-payload='{"optimize_type":"all"}'>Run</button>
                            </div>
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Create Storage Link</div>
                                    <div class="action-desc">Runs <code>storage:link</code> for public file access.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-outline js-action" data-method="POST" data-url="{{ route('system.update.storage-link') }}">Run</button>
                            </div>
                            <div class="system-action debug-action">
                                <div>
                                    <div class="action-title">Debug Mode</div>
                                    <div class="action-desc">Updates <code>APP_DEBUG</code> in <code>.env</code> and clears config cache.</div>
                                </div>
                                <div class="debug-toggle-wrap">
                                    <span id="debugStatus" class="debug-status {{ $systemInfo['app_debug'] ? 'is-on' : 'is-off' }}">
                                        {{ $systemInfo['app_debug'] ? 'On' : 'Off' }}
                                    </span>
                                    <label class="debug-switch">
                                        <input type="checkbox" id="debugToggle" {{ $systemInfo['app_debug'] ? 'checked' : '' }}>
                                        <span class="debug-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card modern-card">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-server"></i> System Info</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td><i class="fab fa-php text-primary"></i> PHP Version</td>
                                    <td class="text-right"><strong>{{ $systemInfo['php_version'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fab fa-laravel text-danger"></i> Laravel Version</td>
                                    <td class="text-right"><strong>{{ $systemInfo['laravel_version'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-database text-info"></i> Database</td>
                                    <td class="text-right"><strong>{{ $systemInfo['database_name'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-plug text-success"></i> Connection</td>
                                    <td class="text-right"><strong>{{ $systemInfo['database_connection'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-cog text-warning"></i> Environment</td>
                                    <td class="text-right">
                                        <span class="badge badge-{{ $systemInfo['app_environment'] === 'production' ? 'success' : 'warning' }}">
                                            {{ $systemInfo['app_environment'] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-bug text-danger"></i> Debug Mode</td>
                                    <td class="text-right">
                                        <span class="badge badge-{{ $systemInfo['app_debug'] ? 'danger' : 'success' }}">
                                            {{ $systemInfo['app_debug'] ? 'ON' : 'OFF' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-clock text-secondary"></i> Timezone</td>
                                    <td class="text-right"><strong>{{ $systemInfo['timezone'] }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="tab-backups" role="tabpanel">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card modern-card mb-3">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-plus-circle"></i> Create Backup</h3>
                        </div>
                        <div class="card-body">
                            <form id="backup-create-form" method="POST" action="{{ route('system.create-backup') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Backup Type</label>
                                    <select class="form-control" id="backup-type">
                                        <option value="db">Database only (.sql)</option>
                                        <option value="full">Full backup (DB + files .zip)</option>
                                    </select>
                                </div>
                                <div class="text-muted small mb-3">Uses internal backup service (no terminal required).</div>
                                <button type="submit" class="btn modern-btn modern-btn-primary btn-block">Create Backup</button>
                            </form>
                        </div>
                    </div>

                    <div class="card modern-card mb-3">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-upload"></i> Restore Upload</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('system.restore-upload') }}" enctype="multipart/form-data" onsubmit="return confirm('Restore from uploaded file? This will replace current data.');">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Backup File (.sql or .zip)</label>
                                    <input type="file" name="backup_file" class="form-control" required>
                                </div>
                                <div class="text-muted small mb-3">Restores database from an uploaded backup file.</div>
                                <button type="submit" class="btn modern-btn modern-btn-warning btn-block">Restore Upload</button>
                            </form>
                        </div>
                    </div>

                    <div class="card modern-card">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-broom"></i> Cleanup</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-muted small mb-3">Deletes old backups to save disk space.</div>
                            <button type="button" class="btn modern-btn modern-btn-outline btn-block js-cleanup-backups" data-url="{{ route('system.cleanup-backups') }}">Clean Old Backups</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card modern-card">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-database"></i> Backup Files</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table modern-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Filename</th>
                                            <th>Size</th>
                                            <th>Age</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($backups as $backup)
                                            <tr>
                                                <td><code>{{ $backup['name'] }}</code></td>
                                                <td>{{ $backup['size'] }}</td>
                                                <td>{{ $backup['age'] }}</td>
                                                <td class="text-right">
                                                    <a class="btn btn-sm modern-btn modern-btn-outline" href="{{ route('system.download-backup', $backup['name']) }}">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <form class="d-inline" method="POST" action="{{ route('system.restore-backup') }}" onsubmit="return confirm('Restore this backup? This will replace current data.');">
                                                        @csrf
                                                        <input type="hidden" name="backup_filename" value="{{ $backup['name'] }}">
                                                        <button type="submit" class="btn btn-sm modern-btn modern-btn-warning">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>
                                                    <form class="d-inline" method="POST" action="{{ route('system.delete-backup', $backup['name']) }}" onsubmit="return confirm('Delete this backup file?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm modern-btn modern-btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No backups found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="tab-updates" role="tabpanel">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card modern-card mb-3">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-code-branch"></i> Migrations</h3>
                        </div>
                        <div class="card-body">
                            <div class="mini-stat">
                                <div class="mini-label">Pending</div>
                                <div class="mini-value" id="pending-count-card">{{ $migrationStatus['pending_count'] }}</div>
                                <div class="mini-meta">Completed {{ $migrationStatus['completed_count'] }}</div>
                            </div>
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Run All Migrations</div>
                                    <div class="action-desc">Runs <code>php artisan migrate --force</code>.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-primary js-action" data-method="POST" data-url="{{ route('system.update.migrate') }}" data-payload='{"seed":false}'>Run</button>
                            </div>
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Migrate + Seed</div>
                                    <div class="action-desc">Runs <code>php artisan migrate --seed --force</code>.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-info js-action" data-method="POST" data-url="{{ route('system.update.migrate') }}" data-payload='{"seed":true}'>Run</button>
                            </div>
                        </div>
                    </div>

                    <div class="card modern-card mb-3">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-stethoscope"></i> Diagnostics</h3>
                        </div>
                        <div class="card-body">
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Check Database</div>
                                    <div class="action-desc">Tests DB connection and required tables.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-outline js-action" data-method="GET" data-url="{{ route('system.update.check-database') }}">Run</button>
                            </div>
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Fix Common Issues</div>
                                    <div class="action-desc">Creates storage folders, fixes permissions, clears caches.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-warning js-action" data-method="POST" data-url="{{ route('system.update.fix-issues') }}">Run</button>
                            </div>
                        </div>
                    </div>

                    <div class="card modern-card">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-database"></i> Data Tools</h3>
                        </div>
                        <div class="card-body">
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Run All Seeders</div>
                                    <div class="action-desc">Runs <code>php artisan db:seed --force</code>.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-outline js-action" data-method="POST" data-url="{{ route('system.update.seed') }}" data-confirm="This will run all seeders. Continue?">Run</button>
                            </div>
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Backfill Opening Balances</div>
                                    <div class="action-desc">Rebuilds opening balances based on existing records.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-outline js-action" data-method="POST" data-url="{{ route('system.update.backfill-opening-balances') }}">Run</button>
                            </div>
                            <div class="system-action">
                                <div>
                                    <div class="action-title">Assign Default Godown</div>
                                    <div class="action-desc">Assigns default godown to products without one.</div>
                                </div>
                                <button type="button" class="btn modern-btn modern-btn-outline js-action" data-method="POST" data-url="{{ route('system.update.assign-default-godown') }}">Run</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card modern-card">
                        <div class="card-header modern-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Pending Migrations</h3>
                        </div>
                        <div class="card-body p-0">
                            @if($migrationStatus['pending_count'] > 0)
                                <div class="table-responsive">
                                    <table class="table modern-table mb-0" id="pending-migrations-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Migration</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($migrationStatus['pending'] as $migration)
                                                <tr data-migration="{{ $migration['name'] }}">
                                                    <td><span class="badge badge-secondary">{{ $migration['date'] }}</span></td>
                                                    <td>
                                                        <strong>{{ $migration['description'] }}</strong>
                                                        <div class="text-muted small">{{ $migration['name'] }}</div>
                                                    </td>
                                                    <td class="text-right">
                                                        <button type="button" class="btn btn-sm modern-btn modern-btn-primary js-run-migration" data-migration="{{ $migration['name'] }}">Run</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-4 text-center text-muted">No pending migrations.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

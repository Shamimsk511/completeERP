@extends('layouts.modern-admin')

@section('title', 'Database Update')
@section('page_title', 'Database & System Update')

@section('header_actions')
    <a class="btn modern-btn modern-btn-secondary" href="{{ route('system.index') }}">
        <i class="fas fa-arrow-left"></i> Back to System Tools
    </a>
@stop

@section('page_content')
    @include('system.partials.module-nav')

    <!-- System Status Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card {{ $migrationStatus['pending_count'] > 0 ? 'stats-card-warning' : 'stats-card-success' }}">
                <div class="stats-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $migrationStatus['pending_count'] }}</h3>
                    <p class="stats-label">Pending Migrations</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $migrationStatus['completed_count'] }}</h3>
                    <p class="stats-label">Completed Migrations</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-info">
                <div class="stats-icon">
                    <i class="fab fa-php"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $systemInfo['php_version'] }}</h3>
                    <p class="stats-label">PHP Version</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-danger">
                <div class="stats-icon">
                    <i class="fab fa-laravel"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $systemInfo['laravel_version'] }}</h3>
                    <p class="stats-label">Laravel Version</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <div id="alert-container"></div>

    @if($migrationStatus['pending_count'] > 0)
        <div class="alert alert-warning modern-alert mb-4">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Database Update Required!</strong>
            You have {{ $migrationStatus['pending_count'] }} pending migration(s) that need to be applied for the application to work correctly.
        </div>
    @else
        <div class="alert alert-success modern-alert mb-4">
            <i class="fas fa-check-circle"></i>
            <strong>Database is up to date!</strong>
            All migrations have been applied successfully.
        </div>
    @endif

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card modern-card mb-4">
                <div class="card-header modern-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body modern-card-body">
                    <div class="d-grid gap-3">
                        @if($migrationStatus['pending_count'] > 0)
                            <button type="button" class="btn modern-btn modern-btn-primary btn-block mb-3" id="run-migrations-btn">
                                <i class="fas fa-play"></i> Run All Migrations
                                <span class="badge badge-light ml-2">{{ $migrationStatus['pending_count'] }}</span>
                            </button>
                            <button type="button" class="btn modern-btn modern-btn-info btn-block mb-3" id="run-migrations-seed-btn">
                                <i class="fas fa-database"></i> Run Migrations + Seeders
                            </button>
                        @else
                            <button type="button" class="btn modern-btn modern-btn-secondary btn-block mb-3" disabled>
                                <i class="fas fa-check"></i> All Migrations Applied
                            </button>
                        @endif

                        <button type="button" class="btn modern-btn modern-btn-warning btn-block mb-3" id="clear-cache-btn">
                            <i class="fas fa-broom"></i> Clear All Caches
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-info btn-block mb-3" id="check-database-btn">
                            <i class="fas fa-stethoscope"></i> Check Database
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-success btn-block mb-3" id="fix-issues-btn">
                            <i class="fas fa-wrench"></i> Fix Common Issues
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-secondary btn-block mb-3" id="storage-link-btn">
                            <i class="fas fa-link"></i> Create Storage Link
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-purple btn-block mb-3" id="seed-accounts-btn">
                            <i class="fas fa-coins"></i> Setup Expense Accounts
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-outline btn-block mb-3" id="run-seeders-btn">
                            <i class="fas fa-seedling"></i> Run All Seeders
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-teal btn-block mb-3" id="sync-customers-ledger-btn">
                            <i class="fas fa-users-cog"></i> Sync Customers to Ledger
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-warning btn-block mb-3" id="backfill-opening-balances-btn">
                            <i class="fas fa-balance-scale"></i> Backfill Opening Balances
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-info btn-block mb-3" id="assign-default-godown-btn">
                            <i class="fas fa-warehouse"></i> Assign Default Godown
                        </button>

                        <button type="button" class="btn modern-btn modern-btn-dark btn-block" id="optimize-btn">
                            <i class="fas fa-rocket"></i> Optimize Application
                        </button>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="card modern-card">
                <div class="card-header modern-header info-header">
                    <h3 class="card-title">
                        <i class="fas fa-server"></i> System Information
                    </h3>
                </div>
                <div class="card-body modern-card-body">
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

        <!-- Pending Migrations -->
        <div class="col-lg-8">
            @if($migrationStatus['pending_count'] > 0)
                <div class="card modern-card mb-4">
                    <div class="card-header modern-header warning-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i> Pending Migrations ({{ $migrationStatus['pending_count'] }})
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th width="15%">Date</th>
                                        <th>Migration</th>
                                        <th width="15%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($migrationStatus['pending'] as $migration)
                                        <tr id="migration-{{ md5($migration['name']) }}">
                                            <td>
                                                <span class="badge badge-secondary">{{ $migration['date'] }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $migration['description'] }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $migration['name'] }}</small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm modern-btn modern-btn-primary run-single-migration"
                                                        data-migration="{{ $migration['name'] }}">
                                                    <i class="fas fa-play"></i> Run
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Completed Migrations -->
            <div class="card modern-card">
                <div class="card-header modern-header success-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle"></i> Completed Migrations ({{ $migrationStatus['completed_count'] }})
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table modern-table mb-0">
                            <thead>
                                <tr>
                                    <th width="15%">Date</th>
                                    <th>Migration</th>
                                    <th width="10%">Batch</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_reverse($migrationStatus['completed']) as $migration)
                                    <tr>
                                        <td>
                                            <span class="badge badge-success">{{ $migration['date'] }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $migration['description'] }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $migration['name'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $migration['batch'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Output Modal -->
    <div class="modal fade" id="output-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-terminal"></i> Command Output
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <pre id="command-output" class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Check Modal -->
    <div class="modal fade" id="database-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-database"></i> Database Status
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="database-status-content">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Checking database...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <style>
        .system-module-nav {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
        }

        .system-nav-pills .nav-link {
            color: #374151;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 8px;
        }

        .system-nav-pills .nav-link.active {
            background: var(--app-primary, #4f46e5);
            color: #fff;
        }
    </style>
<link rel="stylesheet" href="/css/modern-admin.css">
<style>
.modern-alert {
    border-radius: 10px;
    border: none;
    padding: 1rem 1.5rem;
}

.modern-alert i {
    margin-right: 0.5rem;
}

.btn-block {
    width: 100%;
    text-align: left;
    padding: 0.75rem 1rem;
}

.btn-block i {
    margin-right: 0.5rem;
    width: 20px;
    text-align: center;
}

.btn-block .badge {
    float: right;
    margin-top: 2px;
}

.modern-btn-purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border: none;
}

.modern-btn-purple:hover {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    color: white;
}

.modern-btn-teal {
    background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    color: white;
    border: none;
}

.modern-btn-teal:hover {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: white;
}

pre {
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 0.85rem;
    line-height: 1.5;
}

.table-sm td {
    padding: 0.5rem;
    border: none;
}

.modern-modal .modal-content {
    border: none;
    border-radius: 12px;
}

.modern-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0;
}

/* Loading state for buttons */
.btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Migration row states */
.migration-running {
    background-color: rgba(102, 126, 234, 0.1);
}

.migration-success {
    background-color: rgba(40, 167, 69, 0.1);
}

.migration-error {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>
@stop

@section('additional_js')
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';

    // Show alert message
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible modern-alert">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'danger' ? 'exclamation-circle' : 'info-circle')}"></i>
                ${message}
            </div>
        `;
        $('#alert-container').html(alertHtml);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('#alert-container .alert').fadeOut();
        }, 5000);
    }

    // Set button loading state
    function setButtonLoading($btn, loading) {
        if (loading) {
            $btn.addClass('loading');
            $btn.data('original-html', $btn.html());
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        } else {
            $btn.removeClass('loading');
            $btn.html($btn.data('original-html'));
        }
    }

    // Run all migrations
    $('#run-migrations-btn').click(function() {
        const $btn = $(this);

        if (!confirm('Are you sure you want to run all pending migrations? This may take some time.')) {
            return;
        }

        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.migrate") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            timeout: 300000, // 5 minutes timeout
            success: function(response) {
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert('success', response.message);

                    if (response.output) {
                        $('#command-output').text(response.output);
                        $('#output-modal').modal('show');
                    }

                    // Reload page after 2 seconds
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('danger', response.message || 'Migration failed');
                }
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Migration failed. Please check the error log.');

                if (response.trace) {
                    $('#command-output').text(response.trace);
                    $('#output-modal').modal('show');
                }
            }
        });
    });

    // Run migrations + seeders
    $('#run-migrations-seed-btn').click(function() {
        const $btn = $(this);

        if (!confirm('Run all pending migrations and then seed the database? This may take some time.')) {
            return;
        }

        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.migrate") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { seed: true },
            timeout: 300000,
            success: function(response) {
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert('success', response.message);

                    if (response.output) {
                        $('#command-output').text(response.output);
                        $('#output-modal').modal('show');
                    }

                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('danger', response.message || 'Migration/seed failed');
                }
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Migration/seed failed. Please check the error log.');

                if (response.trace) {
                    $('#command-output').text(response.trace);
                    $('#output-modal').modal('show');
                }
            }
        });
    });

    // Run single migration
    $('.run-single-migration').click(function() {
        const $btn = $(this);
        const migration = $btn.data('migration');
        const $row = $btn.closest('tr');

        if (!confirm(`Run migration: ${migration}?`)) {
            return;
        }

        setButtonLoading($btn, true);
        $row.addClass('migration-running');

        $.ajax({
            url: '{{ route("system.update.migrate-single") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { migration: migration },
            timeout: 120000,
            success: function(response) {
                setButtonLoading($btn, false);
                $row.removeClass('migration-running');

                if (response.success) {
                    $row.addClass('migration-success');
                    showAlert('success', response.message);

                    // Remove row and update count after 1 second
                    setTimeout(() => {
                        $row.fadeOut(() => $row.remove());
                        location.reload();
                    }, 1000);
                } else {
                    $row.addClass('migration-error');
                    showAlert('danger', response.message || 'Migration failed');
                }
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                $row.removeClass('migration-running').addClass('migration-error');
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Migration failed');
            }
        });
    });

    // Clear caches
    $('#clear-cache-btn').click(function() {
        const $btn = $(this);
        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.clear-cache") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                setButtonLoading($btn, false);
                showAlert(response.success ? 'success' : 'danger', response.message);
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                showAlert('danger', 'Failed to clear caches');
            }
        });
    });

    // Check database
    $('#check-database-btn').click(function() {
        const $btn = $(this);
        setButtonLoading($btn, true);

        $('#database-status-content').html(`
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Checking database connection and tables...</p>
            </div>
        `);
        $('#database-modal').modal('show');

        $.ajax({
            url: '{{ route("system.update.check-database") }}',
            type: 'GET',
            success: function(response) {
                setButtonLoading($btn, false);

                let content = '';
                if (response.connected) {
                    content = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Database connected successfully!
                        </div>
                        <table class="table table-bordered">
                            <tr>
                                <th>Database Name</th>
                                <td>${response.database}</td>
                            </tr>
                            <tr>
                                <th>Total Tables</th>
                                <td>${response.table_count}</td>
                            </tr>
                            <tr>
                                <th>Critical Tables</th>
                                <td>
                                    ${response.critical_tables_ok
                                        ? '<span class="badge badge-success">All Present</span>'
                                        : '<span class="badge badge-danger">Missing: ' + response.missing_tables.join(', ') + '</span>'}
                                </td>
                            </tr>
                        </table>
                        <h6>All Tables:</h6>
                        <div class="table-responsive" style="max-height: 200px;">
                            <div class="d-flex flex-wrap">
                                ${response.tables.map(t => `<span class="badge badge-secondary m-1">${t}</span>`).join('')}
                            </div>
                        </div>
                    `;
                } else {
                    content = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> ${response.message}
                        </div>
                    `;
                }

                $('#database-status-content').html(content);
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                $('#database-status-content').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> ${response.message || 'Failed to check database'}
                    </div>
                `);
            }
        });
    });

    // Fix common issues
    $('#fix-issues-btn').click(function() {
        const $btn = $(this);
        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.fix-issues") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                setButtonLoading($btn, false);

                if (response.success) {
                    let fixList = response.fixes.map(f => `<li>${f}</li>`).join('');
                    showAlert('success', `${response.message}<ul class="mt-2 mb-0">${fixList}</ul>`);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                showAlert('danger', 'Failed to fix issues');
            }
        });
    });

    // Create storage link
    $('#storage-link-btn').click(function() {
        const $btn = $(this);
        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.storage-link") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                setButtonLoading($btn, false);
                showAlert(response.success ? 'success' : 'warning', response.message);
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Failed to create storage link');
            }
        });
    });

    // Seed expense accounts (Labour, Transportation, etc.)
    $('#seed-accounts-btn').click(function() {
        const $btn = $(this);

        if (!confirm('This will create/update expense accounts (Labour, Transportation, Other Purchase Expenses). Continue?')) {
            return;
        }

        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.seed-accounts") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                setButtonLoading($btn, false);
                showAlert(response.success ? 'success' : 'warning', response.message);
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Failed to seed expense accounts');
            }
        });
    });

    // Sync Customers to Ledger Accounts
    $('#sync-customers-ledger-btn').click(function() {
        const $btn = $(this);

        if (!confirm('This will sync all existing customers and companies to ledger accounts. Customers will be added to Sundry Debtors and Companies to Sundry Creditors. Continue?')) {
            return;
        }

        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.sync-customers-ledger") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                setButtonLoading($btn, false);
                showAlert(response.success ? 'success' : 'warning', response.message);
                if (response.output) {
                    console.log('Sync output:', response.output);
                }
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Failed to sync customers to ledger accounts');
            }
        });
    });

    // Backfill opening balances for companies/payees
    $('#backfill-opening-balances-btn').click(function() {
        const $btn = $(this);

        if (!confirm('This will backfill opening balances for companies and payees. Continue?')) {
            return;
        }

        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.backfill-opening-balances") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                setButtonLoading($btn, false);
                showAlert(response.success ? 'success' : 'danger', response.message || 'Backfill completed.');
                if (response.output) {
                    $('#command-output').text(response.output);
                    $('#output-modal').modal('show');
                }
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Backfill failed.');
                if (response.trace) {
                    $('#command-output').text(response.trace);
                    $('#output-modal').modal('show');
                }
            }
        });
    });

    // Run all seeders (DatabaseSeeder)
    $('#run-seeders-btn').click(function() {
        const $btn = $(this);

        if (!confirm('This will run the main DatabaseSeeder. Continue?')) {
            return;
        }

        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.seed") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            timeout: 120000,
            success: function(response) {
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert('success', response.message);
                    if (response.output) {
                        $('#command-output').text(response.output);
                        $('#output-modal').modal('show');
                    }
                } else {
                    showAlert('danger', response.message || 'Seeder failed');
                }
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Seeder failed');
            }
        });
    });

    // Assign default godown to products
    $('#assign-default-godown-btn').click(function() {
        const $btn = $(this);

        if (!confirm('This will assign all products to the default godown and seed missing godown stock rows. Continue?')) {
            return;
        }

        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.assign-default-godown") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                setButtonLoading($btn, false);
                showAlert(response.success ? 'success' : 'warning', response.message);
                if (response.output) {
                    $('#command-output').text(response.output);
                    $('#output-modal').modal('show');
                }
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Failed to assign default godown');
            }
        });
    });

    // Optimize application
    $('#optimize-btn').click(function() {
        const $btn = $(this);

        if (!confirm('This will cache your configuration and routes. Continue?')) {
            return;
        }

        setButtonLoading($btn, true);

        $.ajax({
            url: '{{ route("system.update.optimize") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                setButtonLoading($btn, false);
                showAlert(response.success ? 'success' : 'danger', response.message);
            },
            error: function(xhr) {
                setButtonLoading($btn, false);
                showAlert('danger', 'Optimization failed');
            }
        });
    });

    console.log('Database Update module initialized');
});
</script>
@stop

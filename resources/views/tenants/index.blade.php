@extends('layouts.modern-admin')

@section('title', 'Company Management')
@section('page_title', 'Company Management')

@section('header_actions')
    <a href="{{ route('tenants.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> New Company
    </a>
@stop

@section('page_content')
    @php
        $successMessage = session('success');
        $errorMessage = session('error');
    @endphp

    @php
        $totalTenants = $tenants->count();
        $activeTenants = $tenants->where('is_active', true)->count();
        $inactiveTenants = $totalTenants - $activeTenants;
    @endphp

    <div class="card modern-card mb-4">
        <div class="card-header modern-header tenants-header">
            <div>
                <h3 class="card-title">
                    <i class="fas fa-building"></i> Companies Overview
                </h3>
                <div class="tenants-meta">
                    <span>Total: <strong>{{ $totalTenants }}</strong></span>
                    <span>Active: <strong>{{ $activeTenants }}</strong></span>
                    <span>Inactive: <strong>{{ $inactiveTenants }}</strong></span>
                </div>
            </div>
            <div class="tenants-search">
                <i class="fas fa-search"></i>
                <input type="text" id="tenant-search" class="form-control tenants-search-input" placeholder="Search companies...">
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tenants-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Status</th>
                            <th class="text-center">Users</th>
                            <th class="text-center">Customers</th>
                            <th class="text-center">Products</th>
                            <th class="text-center">Invoices</th>
                            <th class="text-center">Transactions</th>
                            <th class="text-center">Accounts</th>
                            <th class="text-center">Backup</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            @php
                                $latestBackup = ($tenantBackups[$tenant->id] ?? collect())->first();
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $tenant->name }}</div>
                                    <small class="text-muted">{{ $tenant->slug }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $tenant->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">{{ number_format(($tenantUsers[$tenant->id] ?? collect())->count() ?: ($tenant->users_count ?? 0)) }}</td>
                                <td class="text-center">{{ number_format($tenant->customers_count ?? 0) }}</td>
                                <td class="text-center">{{ number_format($tenant->products_count ?? 0) }}</td>
                                <td class="text-center">{{ number_format($tenant->invoices_count ?? 0) }}</td>
                                <td class="text-center">{{ number_format($tenant->transactions_count ?? 0) }}</td>
                                <td class="text-center">{{ number_format($tenant->accounts_count ?? 0) }}</td>
                                <td class="text-center">
                                    <div class="backup-cell">
                                        <div class="backup-meta">
                                            <div class="backup-title">
                                                {{ $latestBackup ? 'Latest Backup' : 'No Backup' }}
                                            </div>
                                            <div class="backup-subtitle text-muted">
                                                @if($latestBackup)
                                                    {{ $latestBackup['created_at']->format('d M Y, h:i A') }}
                                                @else
                                                    Create one now
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-2">
                                            <form method="POST" action="{{ route('tenants.backups.create', $tenant) }}"
                                                  onsubmit="return confirm('Create a backup for {{ $tenant->name }} now?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-save"></i> Backup
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    data-toggle="modal" data-target="#tenant-restore-{{ $tenant->id }}">
                                                <i class="fas fa-box-open"></i> Manage
                                            </button>
                                            @if($latestBackup)
                                                <a href="{{ route('tenants.backups.download', [$tenant, $latestBackup['filename']]) }}"
                                                   class="btn btn-sm btn-outline-dark">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                        <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="{{ route('business-settings.index', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-cogs"></i> Settings
                                        </a>
                                        <a href="{{ route('users.index', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm btn-outline-dark">
                                            <i class="fas fa-users"></i> Users
                                        </a>
                                        <a href="{{ route('users.create', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-user-plus"></i> User
                                        </a>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                More
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <button class="dropdown-item" type="button"
                                                        data-toggle="collapse" data-target="#tenant-users-{{ $tenant->id }}"
                                                        aria-expanded="false" aria-controls="tenant-users-{{ $tenant->id }}">
                                                    <i class="fas fa-list mr-2"></i> View Users
                                                </button>
                                                <form method="POST" action="{{ route('tenants.switch') }}">
                                                    @csrf
                                                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-exchange-alt mr-2"></i> Switch
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('tenants.assign-existing') }}"
                                                      onsubmit="return confirm('Assign all unassigned data to {{ $tenant->name }}?')">
                                                    @csrf
                                                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-database mr-2"></i> Assign Data
                                                    </button>
                                                </form>
                                                <div class="dropdown-divider"></div>
                                                <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                                                      onsubmit="return confirm('Delete {{ $tenant->name }}? All related data will be unassigned.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash mr-2"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="collapse" id="tenant-users-{{ $tenant->id }}">
                                <td colspan="10" class="bg-light">
                                    <div class="p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="fw-semibold">
                                                Users for {{ $tenant->name }}
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('users.index', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm btn-outline-dark">
                                                    View All
                                                </a>
                                                <a href="{{ route('users.create', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm btn-outline-success">
                                                    Add User
                                                </a>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th class="text-right">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $usersForTenant = $tenantUsers[$tenant->id] ?? collect();
                                                    @endphp
                                                    @forelse($usersForTenant as $user)
                                                        <tr>
                                                            <td>{{ $user->name }}</td>
                                                            <td>{{ $user->email }}</td>
                                                            <td class="text-right">
                                                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted">
                                                                No users assigned to this company yet.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    No companies found. Create your first company to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @foreach($tenants as $tenant)
        <div class="modal fade" id="tenant-restore-{{ $tenant->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content modern-modal-content">
                    <div class="modal-header modern-modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-undo"></i> Restore {{ $tenant->name }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="backup-panel mb-3">
                            <div class="backup-panel__header">
                                <h6 class="mb-0">Create a new backup</h6>
                            </div>
                            <div class="backup-panel__body">
                                <form method="POST" action="{{ route('tenants.backups.create', $tenant) }}"
                                      onsubmit="return confirm('Create a backup for {{ $tenant->name }} now?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-save"></i> Create Backup
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="backup-panel mb-3">
                            <div class="backup-panel__header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Existing backups</h6>
                                <span class="text-muted small">{{ ($tenantBackups[$tenant->id] ?? collect())->count() }} files</span>
                            </div>
                            <div class="backup-panel__body">
                                @if(($tenantBackups[$tenant->id] ?? collect())->isEmpty())
                                    <div class="text-muted small">No backups yet for this company.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>File</th>
                                                    <th>Size</th>
                                                    <th>Created</th>
                                                    <th class="text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(($tenantBackups[$tenant->id] ?? collect()) as $backup)
                                                    <tr>
                                                        <td>{{ $backup['filename'] }}</td>
                                                        <td>{{ $backup['size'] }}</td>
                                                        <td>{{ $backup['created_at']->format('d M Y, h:i A') }}</td>
                                                        <td class="text-right">
                                                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                                                <a href="{{ route('tenants.backups.download', [$tenant, $backup['filename']]) }}"
                                                                   class="btn btn-sm btn-outline-dark">
                                                                    <i class="fas fa-download"></i> Download
                                                                </a>
                                                                <form method="POST" action="{{ route('tenants.backups.restore', $tenant) }}"
                                                                      onsubmit="return confirm('Restore {{ $tenant->name }} from this backup? This will replace current data.');">
                                                                    @csrf
                                                                    <input type="hidden" name="backup_filename" value="{{ $backup['filename'] }}">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i class="fas fa-undo"></i> Restore
                                                                    </button>
                                                                </form>
                                                                <form method="POST" action="{{ route('tenants.backups.delete', [$tenant, $backup['filename']]) }}"
                                                                      onsubmit="return confirm('Delete this backup file?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="backup-panel">
                            <div class="backup-panel__header">
                                <h6 class="mb-0">Upload and restore</h6>
                            </div>
                            <div class="backup-panel__body">
                                <form method="POST" action="{{ route('tenants.backups.restore', $tenant) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-2">
                                        <label for="backup-file-{{ $tenant->id }}" class="small">Upload backup file (.sql)</label>
                                        <input type="file" class="form-control-file" id="backup-file-{{ $tenant->id }}" name="backup_file" accept=".sql">
                                    </div>
                                    <div class="alert alert-warning small mb-2">
                                        Restoring will replace all data for this company. A safety backup is created before restore.
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-undo"></i> Restore from Upload
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop

@section('css')
<style>
    .tenants-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .tenants-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 0.35rem;
    }

    .tenants-search {
        position: relative;
        min-width: 220px;
        flex: 1;
        max-width: 320px;
    }

    .tenants-search i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .tenants-search-input {
        padding-left: 34px;
        height: 36px;
        font-size: 0.85rem;
    }

    .backup-cell {
        min-width: 180px;
    }

    .backup-title {
        font-weight: 600;
        font-size: 0.85rem;
    }

    .backup-subtitle {
        font-size: 0.75rem;
    }

    .backup-panel {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f9fafb;
        padding: 0.75rem;
    }

    .backup-panel__header {
        margin-bottom: 0.5rem;
        color: #111827;
        font-weight: 600;
    }
</style>
@stop

@section('js')
<script>
    (function() {
        @if(!empty($successMessage) || !empty($errorMessage))
        if (typeof Swal !== 'undefined') {
            @if(!empty($successMessage))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json($successMessage),
                timer: 2500,
                showConfirmButton: false
            });
            @endif
            @if(!empty($errorMessage))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json($errorMessage)
            });
            @endif
        } else {
            @if(!empty($successMessage))
            alert(@json($successMessage));
            @endif
            @if(!empty($errorMessage))
            alert(@json($errorMessage));
            @endif
        }
        @endif

        var input = document.getElementById('tenant-search');
        if (!input) return;

        input.addEventListener('input', function() {
            var query = input.value.toLowerCase();
            var rows = document.querySelectorAll('#tenants-table tbody tr');
            rows.forEach(function(row) {
                if (row.classList.contains('collapse')) {
                    return;
                }
                var text = row.innerText.toLowerCase();
                row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
            });
        });
    })();
</script>
@stop

{{-- resources/views/system/backups.blade.php --}}

@extends('layouts.modern-admin')

@section('title', 'System Management - Backups')

@section('page_title', 'System Backups')

@section('header_actions')
    <a href="{{ route('system.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back to System Tools
    </a>
@stop

@section('page_content')
    @include('system.partials.module-nav')

    {{-- Statistics Cards Row --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-0">Total Backups</h6>
                            <h3 class="mb-0">{{ $stats['total_backups'] }}</h3>
                        </div>
                        <i class="fas fa-database fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-0">Database Only</h6>
                            <h3 class="mb-0">{{ $stats['database_backups'] }}</h3>
                        </div>
                        <i class="fas fa-server fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-0">Full Backups</h6>
                            <h3 class="mb-0">{{ $stats['full_backups'] }}</h3>
                        </div>
                        <i class="fas fa-file-archive fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-0">Total Size</h6>
                            <h3 class="mb-0">{{ $stats['total_size'] }}</h3>
                        </div>
                        <i class="fas fa-hdd fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create New Backup Section --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle mr-2"></i>Create New Backup</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-left-primary h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-database fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Database Backup</h5>
                            <p class="card-text text-muted small">Backup database only. Smaller file size, faster backup. Perfect for daily schedules.</p>
                            <form action="{{ route('system.create-backup') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerHTML='<i class=\"fas fa-spinner fa-spin\"></i> Creating...'; this.form.submit();">
                                    <i class="fas fa-database mr-1"></i> Create Database Backup
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card border-left-success h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-file-archive fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Full Backup</h5>
                            <p class="card-text text-muted small">Backup database + all uploaded files (documents, images, etc). Complete recovery option.</p>
                            <form action="{{ route('system.create-full-backup') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerHTML='<i class=\"fas fa-spinner fa-spin\"></i> Creating...'; this.form.submit();">
                                    <i class="fas fa-file-archive mr-1"></i> Create Full Backup
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Restore from Upload --}}
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-upload mr-2"></i>Restore from Uploaded Backup</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <form action="{{ route('system.restore-upload') }}" method="POST" enctype="multipart/form-data" id="uploadRestoreForm">
                        @csrf
                        <div class="form-group">
                            <label for="backup_file">
                                <strong>Upload Backup File (.sql or .zip)</strong>
                            </label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('backup_file') is-invalid @enderror"
                                           id="backup_file" name="backup_file" accept=".sql,.zip" required>
                                    <label class="custom-file-label" for="backup_file">Choose file...</label>
                                </div>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-warning" id="uploadRestoreBtn" disabled>
                                        <i class="fas fa-upload mr-1"></i> Restore
                                    </button>
                                </div>
                            </div>
                            @error('backup_file')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted mt-2">
                                Accepted formats: .sql (database only) or .zip (full backup). Maximum size: 500MB
                            </small>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-warning mb-0" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Warning
                        </h6>
                        <small>
                            Restoring a backup will replace your current database with the backup data. 
                            Please ensure you have a recent backup before proceeding.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cleanup Old Backups --}}
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-trash-alt mr-2"></i>Cleanup Old Backups</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-0">
                        <strong>Delete Old or Excess Backups</strong><br>
                        <small class="text-muted">
                            Automatically delete backups older than {{ env('BACKUP_KEEP_DAYS', 30) }} days or keep 
                            maximum {{ env('BACKUP_MAX_COUNT', 20) }} backup files to free up disk space.
                        </small>
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <button type="button" class="btn btn-danger" id="cleanupBtn" onclick="confirmCleanup()">
                        <i class="fas fa-trash-alt mr-1"></i> Cleanup Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Available Backups Table --}}
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Available Backups</h5>
                </div>
                <div class="col-auto">
                    <span class="badge badge-light badge-pill">{{ $backups->count() }} backup(s)</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($backups->isEmpty())
                <div class="alert alert-info m-3 mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    No backups found. Create your first backup using the form above.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Filename</th>
                                <th width="100">Type</th>
                                <th width="100">Size</th>
                                <th width="180">Created At</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $index => $backup)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($backup['type'] === 'full')
                                            <i class="fas fa-file-archive text-success mr-2" title="Full Backup"></i>
                                        @else
                                            <i class="fas fa-database text-primary mr-2" title="Database Backup"></i>
                                        @endif
                                        <strong>{{ $backup['filename'] }}</strong>
                                    </td>
                                    <td>
                                        @if($backup['type'] === 'full')
                                            <span class="badge badge-success">Full</span>
                                        @else
                                            <span class="badge badge-primary">DB</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $backup['size'] }}</code>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $backup['created_at']->format('d M Y, h:i A') }}<br>
                                            <span class="text-muted">{{ $backup['created_at']->diffForHumans() }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('system.download-backup', $backup['filename']) }}"
                                               class="btn btn-sm btn-success" 
                                               title="Download backup file"
                                               download>
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-warning restore-backup-btn"
                                                    data-filename="{{ $backup['filename'] }}"
                                                    data-type="{{ $backup['type'] }}"
                                                    title="Restore this backup">
                                                <i class="fas fa-redo"></i> Restore
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger delete-backup-btn"
                                                    data-filename="{{ $backup['filename'] }}"
                                                    title="Delete this backup">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No backups found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Restore Confirmation Modal --}}
<div class="modal fade" id="restoreConfirmModal" tabindex="-1" role="dialog" aria-labelledby="restoreConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="restoreConfirmLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Database Restore
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    <strong>File:</strong> <code id="restoreFilename"></code>
                </p>
                <p class="mb-2">
                    <strong>Type:</strong> <span id="restoreType" class="badge"></span>
                </p>
                <div class="alert alert-danger mt-3 mb-0">
                    <h6 class="alert-heading">⚠️ Warning</h6>
                    <small>
                        This will permanently replace your current database with the backup data. 
                        This action cannot be undone. Are you absolutely sure?
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="restoreForm" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="backup_filename" id="restoreBackupFilename">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-redo mr-1"></i>Yes, Restore Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmLabel">
                    <i class="fas fa-trash-alt mr-2"></i>Confirm Delete Backup
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    Are you sure you want to delete this backup?<br>
                    <code id="deleteFilename" class="text-danger"></code>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt mr-1"></i>Yes, Delete
                    </button>
                </form>
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

    .border-left-primary {
        border-left: .25rem solid #007bff !important;
    }
    .border-left-success {
        border-left: .25rem solid #28a745 !important;
    }
    .btn-group .btn {
        margin-right: 2px;
    }
    .opacity-50 {
        opacity: 0.5;
    }
</style>
@stop

@section('additional_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input label update
    document.getElementById('backup_file')?.addEventListener('change', function(e) {
        const label = e.target.nextElementSibling;
        label.textContent = e.target.files.length > 0 ? e.target.files[0].name : 'Choose file...';
        document.getElementById('uploadRestoreBtn').disabled = e.target.files.length === 0;
    });

    // Restore backup button
    document.querySelectorAll('.restore-backup-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            const type = this.dataset.type;
            document.getElementById('restoreFilename').textContent = filename;
            document.getElementById('restoreBackupFilename').value = filename;
            const badge = document.getElementById('restoreType');
            badge.textContent = type === 'full' ? 'Full Backup' : 'Database Only';
            badge.className = 'badge ' + (type === 'full' ? 'badge-success' : 'badge-primary');
            document.getElementById('restoreForm').action = '{{ route("system.restore-backup") }}';
            $('#restoreConfirmModal').modal('show');
        });
    });

    // Delete backup button
    document.querySelectorAll('.delete-backup-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            document.getElementById('deleteFilename').textContent = filename;
            document.getElementById('deleteForm').action = '{{ route("system.delete-backup", ":filename") }}'.replace(':filename', filename);
            $('#deleteConfirmModal').modal('show');
        });
    });

    // Upload restore form submission
    document.getElementById('uploadRestoreBtn')?.addEventListener('click', function() {
        if (confirm('This will restore the database from the uploaded file. Continue?')) {
            document.getElementById('uploadRestoreForm').submit();
        }
    });
});

// Cleanup confirmation
function confirmCleanup() {
    if (confirm('This will delete old backups and keep only recent ones. Continue?')) {
        fetch('{{ route("system.cleanup-backups") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message, 'Cleanup Completed');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message, 'Error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred during cleanup', 'Error');
        });
    }
}
</script>
@stop

@extends('layouts.modern-admin')

@section('title', 'Edit Account Group - ' . $accountGroup->name)

@section('page_title', 'Edit Account Group: ' . $accountGroup->name)

@section('page_content')
    <form action="{{ route('accounting.account-groups.update', $accountGroup) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-folder"></i> Group Details
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Group Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control modern-input @error('name') is-invalid @enderror" value="{{ old('name', $accountGroup->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Group Code <span class="required">*</span></label>
                            <input type="text" name="code" class="form-control modern-input @error('code') is-invalid @enderror" value="{{ old('code', $accountGroup->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Parent Group</label>
                            <select name="parent_id" class="form-control select2 @error('parent_id') is-invalid @enderror">
                                <option value="">None (Root Level)</option>
                                @foreach($parentGroups as $group)
                                    <option value="{{ $group->id }}" {{ old('parent_id', $accountGroup->parent_id) == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nature</label>
                            <input type="text" class="form-control modern-input" value="{{ ucfirst($accountGroup->nature) }}" readonly>
                            <small class="form-text text-muted">Nature cannot be changed after creation</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Affects Gross Profit <span class="required">*</span></label>
                            <select name="affects_gross_profit" class="form-control modern-select @error('affects_gross_profit') is-invalid @enderror" required>
                                <option value="no" {{ old('affects_gross_profit', $accountGroup->affects_gross_profit) == 'no' ? 'selected' : '' }}>No</option>
                                <option value="yes" {{ old('affects_gross_profit', $accountGroup->affects_gross_profit) == 'yes' ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('affects_gross_profit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control modern-input @error('display_order') is-invalid @enderror" value="{{ old('display_order', $accountGroup->display_order) }}" min="0">
                            @error('display_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control modern-textarea" rows="3">{{ old('description', $accountGroup->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg">
                <i class="fas fa-save"></i> Update Group
            </button>
            <a href="{{ route('accounting.account-groups.index') }}" class="btn btn-outline-secondary btn-lg ml-3">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="card modern-card border-danger mt-4">
        <div class="card-header bg-danger text-white">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle"></i> Danger Zone
            </h3>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Delete this account group</h5>
                    @if($canDelete)
                        <p class="text-muted mb-0">Once deleted, this action cannot be undone.</p>
                    @else
                        <p class="text-danger mb-0">
                            <i class="fas fa-ban"></i> Cannot delete this group:
                            <ul class="mb-0 mt-1">
                                @foreach($deleteReasons as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        </p>
                    @endif
                </div>
                <div>
                    @if($canDelete)
                        <button type="button" class="btn btn-danger" id="delete-group-btn">
                            <i class="fas fa-trash"></i> Delete Group
                        </button>
                    @else
                        <button type="button" class="btn btn-danger" disabled>
                            <i class="fas fa-trash"></i> Delete Group
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#delete-group-btn').on('click', function() {
        Swal.fire({
            title: 'Delete Account Group?',
            html: `Are you sure you want to delete <strong>{{ $accountGroup->name }}</strong>?<br><br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("accounting.account-groups.destroy", $accountGroup) }}',
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Account group deleted successfully.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '{{ route("accounting.account-groups.index") }}';
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to delete account group.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@stop

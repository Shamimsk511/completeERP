<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Linked User (optional)</label>
            <select name="user_id" class="form-control">
                <option value="">-- None --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id ?? '') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>NID</label>
            <input type="text" name="nid" class="form-control" value="{{ old('nid', $employee->nid ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $employee->address ?? '') }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Basic Salary</label>
            <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary', $employee->basic_salary ?? '') }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Join Date</label>
            <input type="date" name="join_date" class="form-control" value="{{ old('join_date', optional($employee->join_date ?? null)->toDateString()) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="active" {{ old('status', $employee->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $employee->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Photo</label>
            <input type="file" name="photo" class="form-control-file">
        </div>
        @if(!empty($employee->photo_path))
            <img src="{{ asset('storage/' . $employee->photo_path) }}" alt="Photo" class="img-thumbnail" style="max-height: 120px;">
        @endif
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Document</label>
            <input type="file" name="document" class="form-control-file">
        </div>
        @if(!empty($employee->file_path))
            <a href="{{ asset('storage/' . $employee->file_path) }}" target="_blank">View Document</a>
        @endif
    </div>
</div>

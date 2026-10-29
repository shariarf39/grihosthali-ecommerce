@extends('admin.layouts.app')

@section('title', $member->exists ? 'Edit Admin' : 'Add Admin')

@section('content')
<form method="POST" action="{{ $member->exists ? route('admin.staff.update', $member) : route('admin.staff.store') }}" autocomplete="off">
    @csrf
    @if($member->exists) @method('PUT') @endif
    <div class="form-grid">
        <div class="card">
            <div class="card-header"><h3>Account</h3></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group"><label>Full Name *</label><input name="name" class="form-control" value="{{ old('name', $member->name) }}" required></div>
                    <div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone', $member->phone) }}"></div>
                </div>
                <div class="form-group"><label>Email (used to sign in) *</label><input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}" required></div>
                <div class="form-row">
                    <div class="form-group"><label>{{ $member->exists ? 'New Password' : 'Password *' }}</label><input type="password" name="password" class="form-control" autocomplete="new-password" @required(! $member->exists)>
                        <div class="hint">{{ $member->exists ? 'Leave blank to keep the current password. ' : '' }}Minimum 8 characters.</div></div>
                    <div class="form-group"><label>Confirm Password</label><input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"></div>
                </div>
                <label class="switch-row">
                    <span><strong>Active</strong><small>Deactivated admins cannot sign in</small></span>
                    <input type="checkbox" name="is_active" value="1" class="switch" @checked(old('is_active', $member->is_active))>
                </label>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Role</h3><a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-light">Manage roles</a></div>
            <div class="card-body">
                @foreach($roles as $role)
                    <label class="role-option">
                        <input type="radio" name="role_id" value="{{ $role->id }}" @checked(old('role_id', $member->role_id) == $role->id) required>
                        <span>
                            <strong>{{ $role->name }}</strong>
                            <small>{{ $role->description }}</small>
                            <em>{{ $role->isSuperAdmin() ? 'Full access' : count($role->permissions ?? []) . ' permissions' }}</em>
                        </span>
                    </label>
                @endforeach
                <button class="btn" style="width:100%;justify-content:center;margin-top:12px"><i class="fa-solid fa-floppy-disk"></i> {{ $member->exists ? 'Save Changes' : 'Create Admin' }}</button>
            </div>
        </div>
    </div>
</form>
@endsection

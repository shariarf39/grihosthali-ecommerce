@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="card" style="max-width:640px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            <div class="form-group"><label>Name</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
            <div class="form-group"><label>Email (login)</label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
            <hr style="border:0;border-top:1px solid var(--border);margin:22px 0">
            <h4>Change Password</h4>
            <div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control"></div>
            <div class="form-row">
                <div class="form-group"><label>New Password</label><input type="password" name="password" class="form-control"></div>
                <div class="form-group"><label>Confirm New Password</label><input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <button class="btn"><i class="fa-solid fa-floppy-disk"></i> Update Profile</button>
        </form>
    </div>
</div>
@endsection

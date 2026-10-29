@extends('admin.layouts.app')

@section('title', 'Admin Users')
@section('subtitle', 'People who can sign in to this admin panel')

@section('content')
<div class="page-actions">
    <a href="{{ route('admin.roles.index') }}" class="btn btn-light"><i class="fa-solid fa-key"></i> Roles & Permissions</a>
    <a href="{{ route('admin.activity') }}" class="btn btn-light"><i class="fa-solid fa-fingerprint"></i> Activity Log</a>
    <span class="spacer"></span>
    <a href="{{ route('admin.staff.create') }}" class="btn"><i class="fa-solid fa-user-plus"></i> Add Admin</a>
</div>

<div class="staff-grid">
    @foreach($staff as $m)
        <div class="card staff-card {{ $m->is_active ? '' : 'inactive' }}">
            <div class="card-body">
                <div class="staff-top">
                    <span class="avatar-lg">{{ mb_substr($m->name, 0, 1) }}</span>
                    <div style="min-width:0">
                        <h3>{{ $m->name }} @if($m->id === auth()->id())<span class="badge badge-info">You</span>@endif</h3>
                        <div class="muted">{{ $m->email }}</div>
                        @if($m->phone)<div class="muted">{{ $m->phone }}</div>@endif
                    </div>
                </div>
                <div class="staff-meta">
                    <span class="badge badge-{{ $m->role?->isSuperAdmin() ? 'warning' : 'primary' }}"><i class="fa-solid fa-key"></i> {{ $m->role?->name ?? 'No role' }}</span>
                    <span class="badge badge-{{ $m->is_active ? 'success' : 'secondary' }}">{{ $m->is_active ? 'Active' : 'Deactivated' }}</span>
                </div>
                <div class="muted" style="font-size:12.5px;margin-top:10px"><i class="fa-regular fa-clock"></i> Last login: {{ $m->last_login_at?->diffForHumans() ?? 'never' }}</div>
                <div class="actions" style="margin-top:14px">
                    <a href="{{ route('admin.staff.edit', $m) }}" class="btn btn-sm btn-light"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                    <a href="{{ route('admin.activity', ['user' => $m->id]) }}" class="btn btn-sm btn-light"><i class="fa-solid fa-fingerprint"></i> Activity</a>
                    @if($m->id !== auth()->id())
                        @include('admin.partials.delete', ['action' => route('admin.staff.destroy', $m), 'confirm' => "Remove {$m->name}'s admin access?"])
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

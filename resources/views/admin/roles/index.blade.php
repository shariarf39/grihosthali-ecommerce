@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')
@section('subtitle', 'Control what each admin can see and do')

@section('content')
<div class="page-actions">
    <a href="{{ route('admin.staff.index') }}" class="btn btn-light"><i class="fa-solid fa-user-shield"></i> Admin Users</a>
    <span class="spacer"></span>
    <a href="{{ route('admin.roles.create') }}" class="btn"><i class="fa-solid fa-plus"></i> New Role</a>
</div>
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Role</th><th>Access</th><th>Admins</th><th></th></tr></thead>
            <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td><strong>{{ $role->name }}</strong> @if($role->is_system)<span class="badge badge-secondary">Built-in</span>@endif<div class="muted">{{ $role->description }}</div></td>
                        <td style="max-width:460px">
                            @if($role->isSuperAdmin())
                                <span class="badge badge-warning"><i class="fa-solid fa-crown"></i> Full access to everything</span>
                            @else
                                @foreach(\App\Support\Permissions::GROUPS as $group => $perms)
                                    @php $has = count(array_intersect(array_keys($perms), $role->permissions ?? [])); @endphp
                                    @if($has)<span class="tag-chip">{{ $group }} {{ $has }}/{{ count($perms) }}</span>@endif
                                @endforeach
                            @endif
                        </td>
                        <td>{{ $role->users_count }}</td>
                        <td><div class="actions">
                            @unless($role->isSuperAdmin())
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-pen-to-square"></i></a>
                            @endunless
                            @if(! $role->is_system && ! $role->users_count)
                                @include('admin.partials.delete', ['action' => route('admin.roles.destroy', $role)])
                            @endif
                        </div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

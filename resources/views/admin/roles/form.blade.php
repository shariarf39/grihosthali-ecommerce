@extends('admin.layouts.app')

@section('title', $role->exists ? 'Edit Role: ' . $role->name : 'New Role')

@section('content')
<form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
    @csrf
    @if($role->exists) @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group"><label>Role Name *</label><input name="name" class="form-control" value="{{ old('name', $role->name) }}" required placeholder="e.g. Order Manager"></div>
                <div class="form-group"><label>Description</label><input name="description" class="form-control" value="{{ old('description', $role->description) }}" placeholder="What this person does"></div>
            </div>
        </div>
    </div>

    @php $current = old('permissions', $role->permissions ?? []); @endphp
    <div class="perm-grid">
        @foreach(\App\Support\Permissions::GROUPS as $group => $perms)
            <div class="card perm-card">
                <div class="card-header">
                    <h3>{{ $group }}</h3>
                    <label class="check" style="margin:0;font-size:13px"><input type="checkbox" data-select-group> All</label>
                </div>
                <div class="card-body">
                    @foreach($perms as $key => $label)
                        <label class="perm-row">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}" class="switch" @checked(in_array($key, $current))>
                            <span>{{ $label }}<code>{{ $key }}</code></span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    <button class="btn" style="height:46px"><i class="fa-solid fa-floppy-disk"></i> Save Role</button>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-light" style="height:46px">Cancel</a>
</form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-select-group]').forEach((all) => {
        const boxes = [...all.closest('.perm-card').querySelectorAll('input[name="permissions[]"]')];
        const sync = () => { all.checked = boxes.every((b) => b.checked); };
        sync();
        all.addEventListener('change', () => boxes.forEach((b) => { b.checked = all.checked; }));
        boxes.forEach((b) => b.addEventListener('change', sync));
    });
</script>
@endpush

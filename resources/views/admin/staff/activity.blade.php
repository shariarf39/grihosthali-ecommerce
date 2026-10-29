@extends('admin.layouts.app')

@section('title', 'Activity Log')
@section('subtitle', 'Every change made in the admin panel')

@section('content')
<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <select name="user" class="form-control">
                <option value="">All admins</option>
                @foreach($staff as $s)<option value="{{ $s->id }}" @selected(request('user') == $s->id)>{{ $s->name }}</option>@endforeach
            </select>
            <input name="q" class="form-control" placeholder="Search action or record" value="{{ request('q') }}">
            <button class="btn btn-light"><i class="fa-solid fa-filter"></i> Filter</button>
        </form>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Record</th><th>IP</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $verb = \Illuminate\Support\Str::afterLast($log->action, '.');
                        $tone = match (true) {
                            in_array($verb, ['destroy', 'deleted']) => 'danger',
                            in_array($verb, ['store', 'created', 'send', 'bulk-send']) => 'success',
                            $log->action === 'login' => 'info',
                            default => 'primary',
                        };
                    @endphp
                    <tr>
                        <td class="muted" style="white-space:nowrap">{{ $log->created_at->format('d M Y') }}<br>{{ $log->created_at->format('h:i A') }}</td>
                        <td>{{ $log->user?->name ?? 'Deleted user' }}</td>
                        <td><span class="badge badge-{{ $tone }}">{{ ucwords(str_replace(['.', '-', '_'], ' ', $log->action)) }}</span></td>
                        <td>{{ $log->description ?: '—' }}</td>
                        <td class="muted">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection

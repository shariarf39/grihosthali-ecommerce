@extends('admin.layouts.app')

@section('title', 'Email Logs')

@section('content')
<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <input name="q" class="form-control" placeholder="Recipient or subject" value="{{ request('q') }}">
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                <option value="failed" @selected(request('status') === 'failed')>Failed</option>
            </select>
            <button class="btn btn-light"><i class="fa-solid fa-filter"></i> Filter</button>
        </form>
        <a href="{{ route('admin.integrations.email') }}" class="btn btn-light"><i class="fa-solid fa-gear"></i> Email settings</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Status</th><th>Type</th><th>To</th><th>Subject</th><th>When</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td><span class="badge badge-{{ $log->status === 'sent' ? 'success' : 'danger' }}">{{ ucfirst($log->status) }}</span></td>
                        <td>{{ \App\Services\Notifier::EVENTS[$log->event][0] ?? ucfirst(str_replace('_', ' ', $log->event)) }}</td>
                        <td>{{ $log->to }}</td>
                        <td>{{ $log->subject }}@if($log->error)<div class="muted" style="color:var(--danger)">{{ \Illuminate\Support\Str::limit($log->error, 160) }}</div>@endif</td>
                        <td class="muted">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No emails logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection

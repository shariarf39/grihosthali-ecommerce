@extends('admin.layouts.app')

@section('title', 'Follow-ups')

@section('content')
<div class="stats" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat"><div class="icon red"><i class="fa-solid fa-circle-exclamation"></i></div><div><small>Overdue</small><strong>{{ $counts['overdue'] }}</strong></div></div>
    <div class="stat"><div class="icon gold"><i class="fa-regular fa-calendar"></i></div><div><small>Due Today</small><strong>{{ $counts['today'] }}</strong></div></div>
    <div class="stat"><div class="icon blue"><i class="fa-regular fa-clock"></i></div><div><small>Upcoming</small><strong>{{ $counts['upcoming'] }}</strong></div></div>
</div>

<div class="status-tabs">
    <a href="{{ route('admin.crm.follow-ups') }}" class="{{ $tab === 'pending' ? 'active' : '' }}">Pending</a>
    <a href="{{ route('admin.crm.follow-ups', ['tab' => 'done']) }}" class="{{ $tab === 'done' ? 'active' : '' }}">Completed</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Due</th><th>Customer</th><th>Task</th><th>Assigned by</th><th></th></tr></thead>
            <tbody>
                @forelse($followUps as $f)
                    @php $overdue = ! $f->completed_at && $f->due_at?->lt(now()->startOfDay()); @endphp
                    <tr>
                        <td>
                            <span class="badge badge-{{ $f->completed_at ? 'success' : ($overdue ? 'danger' : ($f->due_at?->isToday() ? 'warning' : 'info')) }}">
                                {{ $f->completed_at ? 'Done ' . $f->completed_at->format('d M') : $f->due_at?->format('d M, h:i A') }}
                            </span>
                        </td>
                        <td>
                            @if($f->customer)
                                <a href="{{ route('admin.crm.show', $f->customer) }}"><strong>{{ $f->customer->name }}</strong></a>
                                <div class="muted"><a href="tel:{{ $f->customer->phone }}">{{ $f->customer->phone }}</a></div>
                            @endif
                        </td>
                        <td style="max-width:420px">{{ $f->message }}</td>
                        <td class="muted">{{ $f->user?->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.crm.activities.complete', $f) }}">@csrf
                                <button class="btn btn-sm {{ $f->completed_at ? 'btn-light' : '' }}"><i class="fa-solid fa-check"></i> {{ $f->completed_at ? 'Reopen' : 'Done' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No follow-ups here. Schedule them from a customer's profile.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $followUps->links() }}
</div>
@endsection

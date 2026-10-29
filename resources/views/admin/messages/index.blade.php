@extends('admin.layouts.app')

@section('title', 'Contact Messages')

@section('content')
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse($messages as $m)
                    <tr style="{{ $m->is_read ? '' : 'font-weight:600' }}">
                        <td>{{ $m->name }}<div class="muted">{{ $m->phone }} {{ $m->email }}</div></td>
                        <td>{{ $m->subject ?: '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($m->message, 70) }}</td>
                        <td class="muted">{{ $m->created_at->diffForHumans() }}</td>
                        <td><div class="actions">
                            <a href="{{ route('admin.messages.show', $m) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-eye"></i></a>
                            @include('admin.partials.delete', ['action' => route('admin.messages.destroy', $m)])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No messages.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $messages->links() }}
</div>
@endsection

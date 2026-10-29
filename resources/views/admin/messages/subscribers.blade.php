@extends('admin.layouts.app')

@section('title', 'Newsletter Subscribers')

@section('content')
<div class="card" style="max-width:760px">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Email</th><th>Subscribed</th><th></th></tr></thead>
            <tbody>
                @forelse($subscribers as $s)
                    <tr><td>{{ $s->email }}</td><td class="muted">{{ $s->created_at->format('d M Y') }}</td><td>@include('admin.partials.delete', ['action' => route('admin.subscribers.destroy', $s)])</td></tr>
                @empty
                    <tr><td colspan="3" class="empty">No subscribers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $subscribers->links() }}
</div>
@endsection

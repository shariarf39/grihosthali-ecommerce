@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')
<div class="card">
    <div class="card-header"><h3>Static Pages</h3><a href="{{ route('admin.pages.create') }}" class="btn"><i class="fa-solid fa-plus"></i> Add Page</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Title</th><th>URL</th><th>Status</th><th>Updated</th><th></th></tr></thead>
            <tbody>
                @forelse($pages as $p)
                    <tr>
                        <td><strong>{{ $p->title }}</strong></td>
                        <td><a href="{{ route('page.show', $p->slug) }}" target="_blank">/page/{{ $p->slug }}</a></td>
                        <td><span class="badge badge-{{ $p->is_active ? 'success' : 'secondary' }}">{{ $p->is_active ? 'Published' : 'Draft' }}</span></td>
                        <td class="muted">{{ $p->updated_at->diffForHumans() }}</td>
                        <td><div class="actions">
                            <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-pen-to-square"></i></a>
                            @include('admin.partials.delete', ['action' => route('admin.pages.destroy', $p)])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No pages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

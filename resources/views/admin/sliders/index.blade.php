@extends('admin.layouts.app')

@section('title', 'Homepage Sliders')

@section('content')
<div class="card">
    <div class="card-header"><h3>Hero Slides</h3><a href="{{ route('admin.sliders.create') }}" class="btn"><i class="fa-solid fa-plus"></i> Add Slide</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Image</th><th>Title</th><th>Link</th><th>Order</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($sliders as $s)
                    <tr>
                        <td><img src="{{ $s->image_url }}" style="width:160px;height:60px;object-fit:cover;border-radius:6px" alt=""></td>
                        <td>{{ $s->title ?: '—' }}<div class="muted">{{ $s->subtitle }}</div></td>
                        <td class="muted">{{ $s->link ?: '—' }}</td>
                        <td>{{ $s->sort_order }}</td>
                        <td><span class="badge badge-{{ $s->is_active ? 'success' : 'secondary' }}">{{ $s->is_active ? 'Active' : 'Hidden' }}</span></td>
                        <td><div class="actions">
                            <a href="{{ route('admin.sliders.edit', $s) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-pen-to-square"></i></a>
                            @include('admin.partials.delete', ['action' => route('admin.sliders.destroy', $s)])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No slides yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

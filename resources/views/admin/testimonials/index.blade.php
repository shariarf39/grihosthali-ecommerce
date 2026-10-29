@extends('admin.layouts.app')

@section('title', 'Testimonials')

@section('content')
<div class="card">
    <div class="card-header"><h3>Customer Testimonials</h3><a href="{{ route('admin.testimonials.create') }}" class="btn"><i class="fa-solid fa-plus"></i> Add Testimonial</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Name</th><th>Message</th><th>Rating</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($testimonials as $t)
                    <tr>
                        <td><strong>{{ $t->name }}</strong><div class="muted">{{ $t->designation }}</div></td>
                        <td style="max-width:420px">{{ \Illuminate\Support\Str::limit($t->message, 110) }}</td>
                        <td style="color:#f5a623">{{ str_repeat('★', $t->rating) }}</td>
                        <td><span class="badge badge-{{ $t->is_active ? 'success' : 'secondary' }}">{{ $t->is_active ? 'Active' : 'Hidden' }}</span></td>
                        <td><div class="actions">
                            <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-pen-to-square"></i></a>
                            @include('admin.partials.delete', ['action' => route('admin.testimonials.destroy', $t)])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No testimonials yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $testimonials->links() }}
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Coupons')

@section('content')
<div class="card">
    <div class="card-header"><h3>Discount Coupons</h3><a href="{{ route('admin.coupons.create') }}" class="btn"><i class="fa-solid fa-plus"></i> Add Coupon</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Code</th><th>Discount</th><th>Min Order</th><th>Used</th><th>Expires</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($coupons as $c)
                    <tr>
                        <td><strong style="letter-spacing:1px">{{ $c->code }}</strong></td>
                        <td>{{ $c->type === 'percent' ? rtrim(rtrim(number_format($c->value, 2), '0'), '.') . '%' : taka($c->value) }}</td>
                        <td>{{ taka($c->min_order) }}</td>
                        <td>{{ $c->used }}{{ $c->usage_limit ? ' / ' . $c->usage_limit : '' }}</td>
                        <td>{{ $c->expires_at?->format('d M Y') ?? 'Never' }}</td>
                        <td>
                            @if(! $c->is_active)<span class="badge badge-secondary">Inactive</span>
                            @elseif($c->expires_at && $c->expires_at->endOfDay()->isPast())<span class="badge badge-danger">Expired</span>
                            @else<span class="badge badge-success">Active</span>@endif
                        </td>
                        <td><div class="actions">
                            <a href="{{ route('admin.coupons.edit', $c) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-pen-to-square"></i></a>
                            @include('admin.partials.delete', ['action' => route('admin.coupons.destroy', $c)])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $coupons->links() }}
</div>
@endsection

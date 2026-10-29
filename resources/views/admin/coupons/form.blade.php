@extends('admin.layouts.app')

@section('title', $coupon->exists ? 'Edit Coupon' : 'Add Coupon')

@section('content')
<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
            @csrf
            @if($coupon->exists) @method('PUT') @endif
            <div class="form-group"><label>Coupon Code *</label><input name="code" class="form-control" value="{{ old('code', $coupon->code) }}" style="text-transform:uppercase" required></div>
            <div class="form-row">
                <div class="form-group"><label>Type</label>
                    <select name="type" class="form-control">
                        <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed amount (৳)</option>
                        <option value="percent" @selected(old('type', $coupon->type) === 'percent')>Percentage (%)</option>
                    </select>
                </div>
                <div class="form-group"><label>Value *</label><input type="number" step="0.01" name="value" class="form-control" value="{{ old('value', $coupon->value) }}" required></div>
            </div>
            <div class="form-row-3">
                <div class="form-group"><label>Minimum Order (৳)</label><input type="number" step="0.01" name="min_order" class="form-control" value="{{ old('min_order', $coupon->min_order ?? 0) }}"></div>
                <div class="form-group"><label>Usage Limit</label><input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Unlimited"></div>
                <div class="form-group"><label>Expiry Date</label><input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $coupon->expires_at?->toDateString()) }}"></div>
            </div>
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active))> Active</label>
            <button class="btn"><i class="fa-solid fa-floppy-disk"></i> Save Coupon</button>
        </form>
    </div>
</div>
@endsection

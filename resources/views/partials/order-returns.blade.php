{{-- Return status + "Request Return" button for a delivered order (track-order and account pages). --}}
@if($order->returns->count())
    <div class="return-box">
        <h4><i class="fa-solid fa-rotate-left"></i> Return Requests</h4>
        @foreach($order->returns as $r)
            <div class="return-status">
                <div><strong>#{{ $r->return_number }}</strong><small>{{ $r->reason }} · {{ $r->created_at->format('d M Y') }}</small></div>
                <span class="badge badge-{{ $r->status_badge }}">{{ $r->status_label }}</span>
            </div>
        @endforeach
    </div>
@endif
@if($order->isReturnable())
    <div class="return-cta">
        <div><strong>Problem with your order?</strong><span class="bn">ডেলিভারির {{ setting('return_days', 7) }} দিনের মধ্যে রিটার্ন / রিফান্ডের আবেদন করুন।</span></div>
        <a href="{{ route('returns.create', $order->order_number) }}" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i> Request Return</a>
    </div>
@endif

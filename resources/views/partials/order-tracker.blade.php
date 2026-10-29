@if(in_array($order->status, ['cancelled', 'returned']))
    <div class="alert alert-danger" style="margin:20px 0"><i class="fa-solid fa-ban"></i> This order has been {{ strtolower($order->status_label) }}.</div>
@else
    @php
        $steps = ['pending' => ['Placed', 'fa-receipt'], 'confirmed' => ['Confirmed', 'fa-check'], 'processing' => ['Processing', 'fa-box'], 'shipped' => ['Shipped', 'fa-truck'], 'delivered' => ['Delivered', 'fa-house-circle-check']];
        $current = array_search($order->status, array_keys($steps));
    @endphp
    <div class="tracker">
        @foreach($steps as $key => [$label, $icon])
            <div class="step {{ $loop->index <= $current ? 'done' : '' }}">
                <div class="dot"><i class="fa-solid {{ $icon }}"></i></div>{{ $label }}
            </div>
        @endforeach
    </div>
@endif

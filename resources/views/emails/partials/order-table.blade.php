<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:18px 0;font-size:14px;">
    <tr style="background:#f6f3ea;">
        <th align="left" style="padding:10px;">Product</th>
        <th align="center" style="padding:10px;">Qty</th>
        <th align="right" style="padding:10px;">Total</th>
    </tr>
    @foreach($order->items as $item)
        <tr>
            <td style="padding:10px;border-bottom:1px solid #eee;">{{ $item->product_name }}</td>
            <td align="center" style="padding:10px;border-bottom:1px solid #eee;">{{ $item->quantity }}</td>
            <td align="right" style="padding:10px;border-bottom:1px solid #eee;">{{ taka($item->total) }}</td>
        </tr>
    @endforeach
    <tr><td colspan="2" align="right" style="padding:6px 10px;color:#777;">Subtotal</td><td align="right" style="padding:6px 10px;">{{ taka($order->subtotal) }}</td></tr>
    <tr><td colspan="2" align="right" style="padding:6px 10px;color:#777;">Delivery ({{ $order->shipping_area }})</td><td align="right" style="padding:6px 10px;">{{ taka($order->shipping_charge) }}</td></tr>
    @if($order->discount > 0)
        <tr><td colspan="2" align="right" style="padding:6px 10px;color:#777;">Discount</td><td align="right" style="padding:6px 10px;">−{{ taka($order->discount) }}</td></tr>
    @endif
    <tr><td colspan="2" align="right" style="padding:10px;font-weight:700;">Total</td><td align="right" style="padding:10px;font-weight:700;color:{{ setting('primary_color', '#1f4d36') }};font-size:17px;">{{ taka($order->total) }}</td></tr>
</table>

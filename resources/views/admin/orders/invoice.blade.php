<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; color: #222; font-size: 13px; margin: 0; background: #f4f4f4; }
        .invoice { max-width: 800px; margin: 24px auto; background: #fff; padding: 40px; border-top: 6px solid #1f4d36; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .brand-bn { font-family: 'Noto Serif Bengali', serif; font-size: 30px; color: #1f4d36; line-height: 1; }
        .brand-en { letter-spacing: 3px; font-size: 11px; margin-top: 4px; }
        h1 { margin: 0; font-size: 28px; color: #c9a24a; letter-spacing: 2px; }
        .meta { display: flex; justify-content: space-between; margin: 24px 0; gap: 20px; }
        .meta h4 { margin: 0 0 6px; color: #1f4d36; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1f4d36; color: #fff; text-align: left; padding: 10px; font-weight: 500; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .totals td { border: 0; padding: 5px 10px; }
        .grand td { font-size: 16px; font-weight: 600; color: #1f4d36; border-top: 2px solid #1f4d36; padding-top: 10px; }
        .foot { margin-top: 40px; text-align: center; color: #777; font-size: 12px; }
        .print-btn { text-align: center; margin: 20px; }
        .print-btn button { background: #1f4d36; color: #fff; border: 0; padding: 10px 24px; border-radius: 6px; cursor: pointer; font: inherit; }
        @media print { body { background: #fff; } .invoice { margin: 0; box-shadow: none; } .print-btn { display: none; } }
    </style>
</head>
<body>
<div class="print-btn"><button onclick="window.print()">Print Invoice</button></div>
<div class="invoice">
    <div class="head">
        <div>
            <div class="brand-bn">গৃহস্থালী</div>
            <div class="brand-en">{{ strtoupper(setting('site_name', 'Grihosthali')) }}</div>
            <div style="margin-top:10px;color:#666">{{ setting('address') }}<br>{{ setting('phone') }} · {{ setting('email') }}</div>
        </div>
        <div style="text-align:right">
            <h1>INVOICE</h1>
            <div>#{{ $order->order_number }}</div>
            <div>{{ $order->created_at->format('d M Y') }}</div>
        </div>
    </div>
    <div class="meta">
        <div>
            <h4>Bill To</h4>
            <strong>{{ $order->name }}</strong><br>{{ $order->phone }}<br>{{ $order->address }}<br>{{ $order->shipping_area }}
        </div>
        <div style="text-align:right">
            <h4>Payment</h4>
            {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : strtoupper($order->payment_method) }}<br>
            Status: {{ ucfirst($order->payment_status) }}<br>
            Order: {{ $order->status_label }}
        </div>
    </div>
    <table>
        <thead><tr><th>#</th><th>Product</th><th>Price</th><th>Qty</th><th style="text-align:right">Total</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $item->product_name }}</td><td>{{ taka($item->price) }}</td><td>{{ $item->quantity }}</td><td style="text-align:right">{{ taka($item->total) }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <table style="width:300px;margin-left:auto;margin-top:12px">
        <tr class="totals"><td>Subtotal</td><td style="text-align:right">{{ taka($order->subtotal) }}</td></tr>
        <tr class="totals"><td>Delivery</td><td style="text-align:right">{{ taka($order->shipping_charge) }}</td></tr>
        @if($order->discount > 0)<tr class="totals"><td>Discount</td><td style="text-align:right">−{{ taka($order->discount) }}</td></tr>@endif
        <tr class="grand"><td>Total</td><td style="text-align:right">{{ taka($order->total) }}</td></tr>
    </table>
    @if($order->note)<p><strong>Note:</strong> {{ $order->note }}</p>@endif
    <div class="foot">Thank you for shopping with {{ setting('site_name', 'Grihosthali') }} — খাঁটি স্বাদ, সবার রান্নায়</div>
</div>
</body>
</html>

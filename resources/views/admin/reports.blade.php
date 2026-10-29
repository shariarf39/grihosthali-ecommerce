@extends('admin.layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="card">
    <div class="card-body">
        <form class="filters" method="GET">
            <label>From <input type="date" name="from" class="form-control" value="{{ $from->toDateString() }}"></label>
            <label>To <input type="date" name="to" class="form-control" value="{{ $to->toDateString() }}"></label>
            <button class="btn"><i class="fa-solid fa-filter"></i> Generate</button>
            <button type="button" class="btn btn-light" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
        </form>
    </div>
</div>

<div class="stats">
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-bag-shopping"></i></div><div><small>Orders</small><strong>{{ $summary['orders'] }}</strong></div></div>
    <div class="stat"><div class="icon green"><i class="fa-solid fa-coins"></i></div><div><small>Product Sales</small><strong>{{ taka($summary['subtotal']) }}</strong></div></div>
    <div class="stat"><div class="icon gold"><i class="fa-solid fa-truck"></i></div><div><small>Delivery Charges</small><strong>{{ taka($summary['shipping']) }}</strong></div></div>
    <div class="stat"><div class="icon red"><i class="fa-solid fa-sack-dollar"></i></div><div><small>Net Total (after {{ taka($summary['discount']) }} discount)</small><strong>{{ taka($summary['total']) }}</strong></div></div>
</div>

<div class="card">
    <div class="card-header"><h3>Product-wise sales ({{ $from->format('d M Y') }} – {{ $to->format('d M Y') }})</h3></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Product</th><th>Quantity Sold</th><th>Amount</th></tr></thead>
            <tbody>
                @forelse($products as $row)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $row->product_name }}</td><td>{{ $row->qty }}</td><td>{{ taka($row->amount) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="empty">No sales in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

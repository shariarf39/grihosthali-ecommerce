@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="stats">
    <div class="stat"><div class="icon green"><i class="fa-solid fa-sack-dollar"></i></div><div><small>Today's Sales</small><strong>{{ taka($stats['today_sales']) }}</strong><small>{{ $stats['today_orders'] }} orders today</small></div></div>
    <div class="stat"><div class="icon gold"><i class="fa-solid fa-calendar"></i></div><div><small>This Month</small><strong>{{ taka($stats['month_sales']) }}</strong><small>Delivered total: {{ taka($stats['total_sales']) }}</small></div></div>
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-bag-shopping"></i></div><div><small>Total Orders</small><strong>{{ $stats['total_orders'] }}</strong><small><a href="{{ route('admin.orders.index', ['status' => 'pending']) }}">{{ $stats['pending'] }} pending</a></small></div></div>
    <div class="stat"><div class="icon red"><i class="fa-solid fa-users"></i></div><div><small>Customers / Products</small><strong>{{ $stats['customers'] }} / {{ $stats['products'] }}</strong><small><a href="{{ route('admin.products.index', ['stock' => 'low']) }}">{{ $stats['low_stock'] }} low stock</a></small></div></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>Sales — last 14 days</h3></div>
        <div class="card-body"><canvas id="salesChart" height="110"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header"><h3>Orders by status</h3></div>
        <div class="card-body">
            <ul class="info-list">
                @foreach(\App\Models\Order::STATUSES as $key => $label)
                    <li><span><span class="badge badge-{{ status_badge($key) }}">{{ $label }}</span></span><span><a href="{{ route('admin.orders.index', ['status' => $key]) }}">{{ $statusCounts[$key] ?? 0 }}</a></span></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>Recent Orders</h3><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light">View all</a></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}"><strong>#{{ $order->order_number }}</strong></a></td>
                            <td>{{ $order->name }}<div class="muted">{{ $order->phone }}</div></td>
                            <td>{{ taka($order->total) }}</td>
                            <td><span class="badge badge-{{ status_badge($order->status) }}">{{ $order->status_label }}</span></td>
                            <td class="muted">{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-header"><h3>Top Selling</h3></div>
            <div class="table-wrap">
                <table class="table">
                    @foreach($topProducts as $p)
                        <tr><td><img src="{{ $p->image_url }}" class="thumb" alt=""></td><td>{{ \Illuminate\Support\Str::limit($p->name, 28) }}<div class="muted">{{ $p->sold_count }} sold</div></td></tr>
                    @endforeach
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Low Stock</h3></div>
            <div class="table-wrap">
                <table class="table">
                    @forelse($lowStock as $p)
                        <tr><td><a href="{{ route('admin.products.edit', $p) }}">{{ \Illuminate\Support\Str::limit($p->name, 30) }}</a></td><td><span class="badge badge-danger">{{ $p->stock }} left</span></td></tr>
                    @empty
                        <tr><td class="empty">All products are well stocked 🎉</td></tr>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const chartData = @json($chart);
    new Chart(document.getElementById('salesChart'), {
        data: {
            labels: chartData.map(d => d.label),
            datasets: [
                { type: 'bar', label: 'Sales (৳)', data: chartData.map(d => d.total), backgroundColor: 'rgba(31,77,54,.85)', borderRadius: 6, yAxisID: 'y' },
                { type: 'line', label: 'Orders', data: chartData.map(d => d.orders), borderColor: '#c9a24a', backgroundColor: '#c9a24a', tension: .35, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush

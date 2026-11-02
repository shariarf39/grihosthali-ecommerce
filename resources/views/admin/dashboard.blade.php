@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="stats">
    <div class="stat"><div class="icon green"><i class="fa-solid fa-sack-dollar"></i></div><div><small>Today's Sales</small><strong>{{ taka($stats['today_sales']) }}</strong><small>{{ $stats['today_orders'] }} orders today</small></div></div>
    <div class="stat"><div class="icon gold"><i class="fa-solid fa-calendar"></i></div><div><small>This Month</small><strong>{{ taka($stats['month_sales']) }}</strong><small>Gross profit ≈ {{ taka($stats['month_profit']) }}</small></div></div>
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-bag-shopping"></i></div><div><small>Total Orders</small><strong>{{ $stats['total_orders'] }}</strong><small><a href="{{ route('admin.orders.index', ['status' => 'pending']) }}">{{ $stats['pending'] }} pending</a></small></div></div>
    <div class="stat"><div class="icon red"><i class="fa-solid fa-users"></i></div><div><small>Customers / Products</small><strong>{{ $stats['customers'] }} / {{ $stats['products'] }}</strong><small><a href="{{ route('admin.inventory.index', ['filter' => 'low']) }}">{{ $stats['low_stock'] }} low stock</a> · <a href="{{ route('admin.returns.index', ['status' => 'requested']) }}">{{ $stats['returns'] }} returns</a></small></div></div>
</div>

@php
    $statusTotal = $statusCounts->sum();
    $pendingCount = (int) ($statusCounts['pending'] ?? 0);
    $deliveredCount = (int) ($statusCounts['delivered'] ?? 0);
    $openCount = collect(['pending', 'confirmed', 'processing', 'shipped'])
        ->sum(fn ($status) => (int) ($statusCounts[$status] ?? 0));
    $exceptionCount = (int) ($statusCounts['cancelled'] ?? 0) + (int) ($statusCounts['returned'] ?? 0);
    $deliveryRate = $statusTotal > 0 ? (int) round($deliveredCount / $statusTotal * 100) : 0;
    $exceptionRate = $statusTotal > 0 ? (int) round($exceptionCount / $statusTotal * 100) : 0;
    $statusIcons = [
        'pending' => 'fa-regular fa-clock',
        'confirmed' => 'fa-solid fa-circle-check',
        'processing' => 'fa-solid fa-gear',
        'shipped' => 'fa-solid fa-truck-fast',
        'delivered' => 'fa-solid fa-box-open',
        'cancelled' => 'fa-solid fa-ban',
        'returned' => 'fa-solid fa-arrow-rotate-left',
    ];
@endphp
<section class="card dashboard-statuses" aria-labelledby="orders-by-status-title">
    <div class="card-header dashboard-statuses-header">
        <div>
            <h3 id="orders-by-status-title"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Orders by status</h3>
            <small>Track order fulfillment across every stage</small>
        </div>
        <div class="dashboard-status-total">
            <strong>{{ number_format($statusTotal) }}</strong>
            <span>{{ \Illuminate\Support\Str::plural('order', $statusTotal) }} total</span>
        </div>
    </div>
    <div class="card-body">
        <div class="order-status-insights">
            <div class="order-status-insight order-status-insight--success">
                <span class="order-status-insight-icon"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
                <span class="order-status-insight-copy">
                    <span>Fulfillment rate</span>
                    <strong>{{ $deliveryRate }}%</strong>
                    <small>{{ number_format($deliveredCount) }} of {{ number_format($statusTotal) }} orders delivered</small>
                </span>
            </div>
            <div class="order-status-insight order-status-insight--primary">
                <span class="order-status-insight-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                <span class="order-status-insight-copy">
                    <span>Active pipeline</span>
                    <strong>{{ number_format($openCount) }}</strong>
                    <small>{{ number_format($pendingCount) }} awaiting confirmation</small>
                </span>
            </div>
            <div class="order-status-insight order-status-insight--warning">
                <span class="order-status-insight-icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                <span class="order-status-insight-copy">
                    <span>Cancelled &amp; returned</span>
                    <strong>{{ number_format($exceptionCount) }} <small>({{ $exceptionRate }}%)</small></strong>
                    <small>Share of all orders</small>
                </span>
            </div>
        </div>
        <div class="order-status-grid">
            @foreach(\App\Models\Order::STATUSES as $key => $label)
                @php
                    $count = (int) ($statusCounts[$key] ?? 0);
                    $percentage = $statusTotal > 0 ? (int) round($count / $statusTotal * 100) : 0;
                    $color = status_badge($key);
                @endphp
                <a class="order-status-card order-status-card--{{ $color }}" href="{{ route('admin.orders.index', ['status' => $key]) }}">
                    <span class="order-status-icon"><i class="{{ $statusIcons[$key] ?? 'fa-solid fa-circle' }}" aria-hidden="true"></i></span>
                    <span class="order-status-details">
                        <span class="order-status-label">{{ $label }}</span>
                        <strong>{{ number_format($count) }}</strong>
                    </span>
                    <span class="order-status-progress" role="progressbar" aria-label="{{ $label }} orders" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                        <span style="width: {{ $percentage }}%"></span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<div class="card dashboard-sales">
    <div class="card-header"><h3>Sales — last 14 days</h3></div>
    <div class="card-body"><canvas id="salesChart" height="110"></canvas></div>
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
            <div class="card-header"><h3><i class="fa-regular fa-bell" style="color:var(--gold)"></i> Follow-ups Due</h3><a href="{{ route('admin.crm.follow-ups') }}" class="btn btn-sm btn-light">All</a></div>
            <div class="table-wrap">
                <table class="table">
                    @forelse($followUps as $f)
                        <tr><td><a href="{{ route('admin.crm.show', $f->crm_customer_id) }}">{{ $f->customer?->name }}</a><div class="muted">{{ \Illuminate\Support\Str::limit($f->message, 40) }}</div></td>
                            <td><span class="badge badge-{{ $f->due_at->isPast() ? 'danger' : 'warning' }}">{{ $f->due_at->format('d M') }}</span></td></tr>
                    @empty
                        <tr><td class="empty">No follow-ups due today.</td></tr>
                    @endforelse
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Low Stock</h3><a href="{{ route('admin.inventory.index', ['filter' => 'low']) }}" class="btn btn-sm btn-light">Inventory</a></div>
            <div class="table-wrap">
                <table class="table">
                    @forelse($lowStock as $p)
                        <tr><td><a href="{{ route('admin.inventory.index', ['q' => $p->name]) }}">{{ \Illuminate\Support\Str::limit($p->name, 30) }}</a></td><td><span class="badge badge-{{ $p->stock ? 'warning' : 'danger' }}">{{ $p->stock }} left</span></td></tr>
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
                { type: 'bar', label: 'Sales (৳)', data: chartData.map(d => d.total), backgroundColor: 'rgba(8,70,69,.85)', borderRadius: 6, yAxisID: 'y' },
                { type: 'line', label: 'Orders', data: chartData.map(d => d.orders), borderColor: '#e0a421', backgroundColor: '#e0a421', tension: .35, yAxisID: 'y1' }
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

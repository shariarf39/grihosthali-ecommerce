{{-- Courier booking panel on the order page. --}}
@php
    $manager = app(\App\Services\Couriers\CourierManager::class);
    $available = $manager->available();
    $shipment = $order->activeShipment;
    $default = old('courier', setting('courier_default'));
@endphp
<div class="card courier-panel">
    <div class="card-header">
        <h3><i class="fa-solid fa-truck-fast" style="color:var(--gold)"></i> Courier</h3>
        @if($shipment)<span class="badge badge-{{ $shipment->status_badge }}">{{ $shipment->status_label }}</span>@endif
    </div>
    <div class="card-body">
        @if($shipment)
            <div class="consignment">
                <span class="courier-logo {{ $shipment->courier }}">{{ mb_substr($shipment->courier_label, 0, 1) }}</span>
                <div>
                    <strong>{{ $shipment->courier_label }}</strong>
                    <div class="muted">Consignment: <strong style="color:var(--title)">{{ $shipment->consignment_id }}</strong></div>
                    @if($shipment->tracking_code && $shipment->tracking_code !== $shipment->consignment_id)<div class="muted">Tracking: {{ $shipment->tracking_code }}</div>@endif
                </div>
            </div>
            <ul class="info-list" style="margin:12px 0">
                <li><span>COD amount</span><span>{{ taka($shipment->cod_amount) }}</span></li>
                @if($shipment->delivery_fee)<li><span>Delivery fee</span><span>{{ taka($shipment->delivery_fee) }}</span></li>@endif
                <li><span>Booked</span><span>{{ $shipment->created_at->format('d M, h:i A') }} · {{ $shipment->user?->name ?? 'System' }}</span></li>
                <li><span>Last sync</span><span>{{ $shipment->last_synced_at?->diffForHumans() ?? '—' }}</span></li>
            </ul>
            <div class="actions" style="flex-wrap:wrap">
                <form method="POST" action="{{ route('admin.shipments.sync', $shipment) }}">@csrf<button class="btn btn-sm"><i class="fa-solid fa-arrows-rotate"></i> Refresh status</button></form>
                @if($shipment->tracking_url)<a href="{{ $shipment->tracking_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-light"><i class="fa-solid fa-location-dot"></i> Track</a>@endif
                @if($manager->driver($shipment->courier)->supportsCancel())
                    <form method="POST" action="{{ route('admin.shipments.cancel', $shipment) }}" data-confirm="Cancel this booking with {{ $shipment->courier_label }}?">@csrf<button class="btn btn-sm btn-light"><i class="fa-solid fa-ban"></i> Cancel booking</button></form>
                @endif
                <form method="POST" action="{{ route('admin.shipments.detach', $shipment) }}" data-confirm="Detach this booking? Only do this if it was cancelled in the courier panel.">@csrf<button class="btn btn-sm btn-light" title="Detach so the order can be re-sent"><i class="fa-solid fa-link-slash"></i></button></form>
            </div>
        @elseif(! $available)
            <p class="muted" style="margin:0">No courier is connected yet.</p>
            @can('integrations.manage')<a href="{{ route('admin.integrations.couriers') }}" class="btn btn-light" style="margin-top:10px"><i class="fa-solid fa-plug"></i> Connect a courier</a>@endcan
        @elseif(in_array($order->status, ['cancelled', 'returned', 'delivered']))
            <p class="muted" style="margin:0">This order is {{ strtolower($order->status_label) }} — no booking needed.</p>
        @else
            <form method="POST" action="{{ route('admin.shipments.send', $order) }}" id="courier-form">
                @csrf
                <div class="courier-choice">
                    @foreach($available as $d)
                        <label>
                            <input type="radio" name="courier" value="{{ $d->slug() }}" data-locations="{{ $d->supportsLocations() ? 1 : 0 }}"
                                   @checked($default === $d->slug() || (! $default && $loop->first))>
                            <span><span class="courier-logo sm {{ $d->slug() }}">{{ mb_substr($d->label(), 0, 1) }}</span> {{ $d->label() }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="form-row">
                    <div class="form-group"><label>COD amount (৳)</label><input type="number" step="1" min="0" name="cod_amount" class="form-control" value="{{ old('cod_amount', $order->payment_status === 'paid' ? 0 : (int) round($order->total)) }}"></div>
                    <div class="form-group"><label>Weight</label><input type="number" step="0.1" min="0" name="weight" class="form-control" value="{{ old('weight') }}" placeholder="Default"></div>
                </div>
                <div class="location-fields" hidden>
                    <div class="form-group"><label>City</label><select name="city_id" class="form-control" data-loc="cities"><option value="">Auto-detect from address</option></select></div>
                    <div class="form-row">
                        <div class="form-group"><label>Zone</label><select name="zone_id" class="form-control" data-loc="zones" disabled><option value="">—</option></select></div>
                        <div class="form-group"><label>Area</label><select name="area_id" class="form-control" data-loc="areas" disabled><option value="">—</option></select></div>
                    </div>
                </div>
                <div class="form-group"><label>Note for rider</label><input name="note" class="form-control" maxlength="250" value="{{ old('note', $order->note) }}" placeholder="e.g. Call before delivery"></div>
                <button class="btn" style="width:100%;justify-content:center"><i class="fa-solid fa-paper-plane"></i> Send to courier</button>
            </form>
        @endif

        @if($order->shipments->where('is_active', false)->count())
            <details style="margin-top:14px"><summary class="muted" style="cursor:pointer">Previous bookings ({{ $order->shipments->where('is_active', false)->count() }})</summary>
                @foreach($order->shipments->where('is_active', false) as $old)
                    <div class="muted" style="font-size:12.5px;margin-top:6px">{{ $old->courier_label }} · {{ $old->consignment_id }} · {{ $old->status_label }} · {{ $old->created_at->format('d M Y') }}</div>
                @endforeach
            </details>
        @endif
    </div>
</div>

@if(! $shipment && $available)
@push('scripts')
<script>
(function () {
    const form = document.getElementById('courier-form');
    if (!form) return;
    const box = form.querySelector('.location-fields');
    const base = @json(url('admin/couriers'));
    const sel = (n) => form.querySelector(`[data-loc="${n}"]`);
    const courier = () => form.querySelector('input[name=courier]:checked');

    async function load(select, params, placeholder) {
        select.disabled = true;
        select.innerHTML = '<option value="">Loading…</option>';
        try {
            const res = await fetch(`${base}/${courier().value}/locations?${new URLSearchParams(params)}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed');
            select.innerHTML = `<option value="">${placeholder}</option>` + data.map((r) => `<option value="${r.id}">${r.name}</option>`).join('');
            select.disabled = false;
        } catch (e) {
            select.innerHTML = `<option value="">Could not load (${e.message.slice(0, 60)})</option>`;
        }
    }
    function reset() {
        ['zones', 'areas'].forEach((n) => { sel(n).innerHTML = '<option value="">—</option>'; sel(n).disabled = true; });
        const needs = courier().dataset.locations === '1';
        box.hidden = !needs;
        if (needs) load(sel('cities'), { type: 'cities' }, 'Auto-detect from address');
    }
    form.querySelectorAll('input[name=courier]').forEach((r) => r.addEventListener('change', reset));
    sel('cities').addEventListener('change', (e) => {
        sel('areas').innerHTML = '<option value="">—</option>'; sel('areas').disabled = true;
        if (e.target.value) load(sel('zones'), { type: 'zones', city: e.target.value }, 'Select zone');
    });
    sel('zones').addEventListener('change', (e) => {
        if (e.target.value) load(sel('areas'), { type: 'areas', zone: e.target.value, city: sel('cities').value }, 'Select area (optional)');
    });
    reset();
})();
</script>
@endpush
@endif

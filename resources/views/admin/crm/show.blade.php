@extends('admin.layouts.app')

@section('title', $customer->name)

@section('content')
@php $wa = preg_replace('/^0/', '880', $customer->phone); @endphp
<div class="profile-head card">
    <div class="card-body" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">
        <span class="avatar-lg">{{ mb_substr($customer->name, 0, 1) }}</span>
        <div style="flex:1;min-width:220px">
            <h2 style="margin:0">{{ $customer->name }}
                <span class="badge badge-{{ \App\Models\CrmCustomer::segmentBadge($customer->segment) }}" style="vertical-align:middle">{{ $customer->segment }}</span>
            </h2>
            <div class="muted" style="margin-top:4px">
                <i class="fa-solid fa-phone"></i> {{ $customer->phone }}
                @if($customer->email) · <i class="fa-regular fa-envelope"></i> {{ $customer->email }}@endif
                · Customer since {{ $customer->created_at->format('M Y') }}
            </div>
            <div style="margin-top:8px">@foreach($customer->tags ?? [] as $t)<span class="tag-chip">{{ $t }}</span>@endforeach</div>
        </div>
        <div class="actions">
            <a href="tel:{{ $customer->phone }}" class="btn"><i class="fa-solid fa-phone"></i> Call</a>
            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn" style="background:#25d366"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
            @if($customer->email)<a href="mailto:{{ $customer->email }}" class="btn btn-light"><i class="fa-regular fa-envelope"></i> Email</a>@endif
        </div>
    </div>
    @if($customer->is_blocked)
        <div class="alert alert-danger" style="margin:0 20px 20px"><i class="fa-solid fa-ban"></i> <strong>Blocked</strong> — this customer cannot place orders on the website. {{ $customer->block_reason }}</div>
    @endif
</div>

<div class="stats">
    <div class="stat"><div class="icon green"><i class="fa-solid fa-sack-dollar"></i></div><div><small>Lifetime Value</small><strong>{{ taka($customer->total_spent ?? 0) }}</strong><small>Avg order {{ taka($avgOrder) }}</small></div></div>
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-bag-shopping"></i></div><div><small>Total Orders</small><strong>{{ $customer->orders_count }}</strong><small>{{ $customer->delivered_count }} delivered</small></div></div>
    <div class="stat"><div class="icon {{ ($customer->success_rate ?? 100) >= 80 ? 'green' : 'red' }}"><i class="fa-solid fa-shield-halved"></i></div><div><small>Delivery Success</small><strong>{{ $customer->success_rate !== null ? $customer->success_rate . '%' : 'N/A' }}</strong><small>{{ $customer->failed_count }} cancelled / returned</small></div></div>
    <div class="stat"><div class="icon gold"><i class="fa-solid fa-rotate-left"></i></div><div><small>Returns</small><strong>{{ $returns->count() }}</strong><small>Last order {{ $customer->last_order_at?->diffForHumans() ?? '—' }}</small></div></div>
</div>

<div class="grid-2">
    <div>
        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-timeline" style="color:var(--gold)"></i> Activity & Notes</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.crm.activities.store', $customer) }}" class="activity-form">
                    @csrf
                    <div class="form-row">
                        <div class="form-group"><label>Type</label>
                            <select name="type" class="form-control" onchange="document.getElementById('due-wrap').style.display = this.value === 'follow_up' ? 'block' : 'none'">
                                @foreach(\App\Models\CrmActivity::TYPES as $k => [$label]) @continue($k === 'system')<option value="{{ $k }}" @selected(old('type') === $k)>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group" id="due-wrap" style="display:{{ old('type') === 'follow_up' ? 'block' : 'none' }}"><label>Follow-up Due</label><input type="datetime-local" name="due_at" class="form-control" value="{{ old('due_at', now()->addDay()->setTime(11, 0)->format('Y-m-d\TH:i')) }}"></div>
                    </div>
                    <div class="form-group"><textarea name="message" rows="2" class="form-control" placeholder="What happened? e.g. Called to confirm order, customer wants delivery after 5pm" required>{{ old('message') }}</textarea></div>
                    <button class="btn"><i class="fa-solid fa-plus"></i> Add</button>
                </form>

                <ul class="timeline">
                    @forelse($activities as $a)
                        <li class="{{ $a->type }} {{ $a->type === 'follow_up' && ! $a->completed_at && $a->due_at?->isPast() ? 'overdue' : '' }}">
                            <span class="tl-icon"><i class="{{ $a->icon }}"></i></span>
                            <div class="tl-body">
                                <div class="tl-head">
                                    <strong>{{ $a->type_label }}</strong>
                                    @if($a->type === 'follow_up')
                                        @if($a->completed_at)<span class="badge badge-success">Done</span>
                                        @else<span class="badge badge-{{ $a->due_at?->isPast() ? 'danger' : 'warning' }}">Due {{ $a->due_at?->format('d M, h:i A') }}</span>@endif
                                    @endif
                                    <span class="muted">{{ $a->created_at->diffForHumans() }} · {{ $a->user?->name ?? 'System' }}</span>
                                </div>
                                <p>{{ $a->message }}</p>
                                @if($a->type !== 'system')
                                    <div class="actions">
                                        @if($a->type === 'follow_up')
                                            <form method="POST" action="{{ route('admin.crm.activities.complete', $a) }}">@csrf<button class="btn btn-sm btn-light">{{ $a->completed_at ? 'Reopen' : 'Mark done' }}</button></form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.crm.activities.destroy', $a) }}" data-confirm="Delete this activity?">@csrf @method('DELETE')<button class="btn btn-sm btn-light"><i class="fa-regular fa-trash-can"></i></button></form>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="muted" style="padding-left:0">No activity yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Order History</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Order</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($orders as $o)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $o) }}"><strong>#{{ $o->order_number }}</strong></a></td>
                                <td>{{ $o->items_count }}</td>
                                <td>{{ taka($o->total) }}</td>
                                <td><span class="badge badge-{{ status_badge($o->status) }}">{{ $o->status_label }}</span></td>
                                <td class="muted">{{ $o->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty">No orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($returns->count())
            <div class="card">
                <div class="card-header"><h3>Returns</h3></div>
                <div class="table-wrap">
                    <table class="table">
                        @foreach($returns as $r)
                            <tr>
                                <td><a href="{{ route('admin.returns.show', $r) }}">#{{ $r->return_number }}</a></td>
                                <td>{{ $r->reason }}</td>
                                <td><span class="badge badge-{{ $r->status_badge }}">{{ $r->status_label }}</span></td>
                                <td class="muted">{{ $r->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><h3>Profile</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.crm.update', $customer) }}">
                @csrf @method('PUT')
                <div class="form-group"><label>Name</label><input name="name" class="form-control" value="{{ old('name', $customer->name) }}" required></div>
                <div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}"></div>
                <div class="form-group"><label>Address</label><textarea name="address" rows="2" class="form-control">{{ old('address', $customer->address) }}</textarea></div>
                <div class="form-group"><label>Source</label>
                    <select name="source" class="form-control">
                        @foreach(['website' => 'Website', 'facebook' => 'Facebook', 'phone' => 'Phone Order', 'whatsapp' => 'WhatsApp', 'referral' => 'Referral', 'offline' => 'Offline / Shop'] as $k => $l)
                            <option value="{{ $k }}" @selected($customer->source === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Tags</label>
                    <div class="tag-checks">
                        @foreach(\App\Models\CrmCustomer::TAGS as $t)
                            <label><input type="checkbox" name="tags[]" value="{{ $t }}" @checked(in_array($t, $customer->tags ?? []))> {{ $t }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group"><label>Internal Notes</label><textarea name="notes" rows="4" class="form-control" placeholder="Preferences, allergies, special pricing…">{{ old('notes', $customer->notes) }}</textarea></div>
                <hr style="border:0;border-top:1px solid var(--border);margin:18px 0">
                <label class="check"><input type="checkbox" name="is_blocked" value="1" @checked($customer->is_blocked)> <span style="color:var(--danger);font-weight:500">Block this customer (fake orders)</span></label>
                <div class="form-group"><input name="block_reason" class="form-control" placeholder="Block reason" value="{{ old('block_reason', $customer->block_reason) }}"></div>
                <button class="btn" style="width:100%;justify-content:center"><i class="fa-solid fa-floppy-disk"></i> Save Profile</button>
            </form>
            @if($customer->user_id)
                <p class="muted" style="margin-top:14px"><i class="fa-solid fa-user-check"></i> Has a website account (<a href="{{ route('admin.customers.show', $customer->user_id) }}">view</a>)</p>
            @endif
        </div>
    </div>
</div>
@endsection

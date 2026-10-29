@extends('admin.layouts.app')

@section('title', 'Courier Integrations')
@section('subtitle', 'Connect Steadfast, Pathao and CarryBee to book parcels from your orders')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.integrations.couriers.general') }}" class="courier-general">
            @csrf
            <div class="form-group" style="margin:0">
                <label>Default courier</label>
                <select name="courier_default" class="form-control">
                    <option value="">Ask every time</option>
                    @foreach($drivers as $d)<option value="{{ $d->slug() }}" @selected(setting('courier_default') === $d->slug())>{{ $d->label() }}</option>@endforeach
                </select>
            </div>
            <label class="check" style="margin:0"><input type="checkbox" name="courier_auto_status" value="1" @checked(setting('courier_auto_status'))>
                Auto-update order status from courier (Delivered / Returned)</label>
            <button class="btn"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        </form>
    </div>
</div>

<div class="courier-grid">
    @foreach($drivers as $d)
        @php
            $slug = $d->slug();
            $stores = json_decode((string) setting("courier_{$slug}_stores"), true) ?: [];
            $ready = $d->enabled() && $d->isConfigured();
        @endphp
        <div class="card courier-card {{ $ready ? 'is-ready' : '' }}">
            <div class="card-header">
                <div class="courier-title">
                    <span class="courier-logo {{ $slug }}">{{ mb_substr($d->label(), 0, 1) }}</span>
                    <div>
                        <h3>{{ $d->label() }}</h3>
                        <small class="muted">
                            @if($ready)<span class="status-dot on"></span> Active{{ $d->config('mode') === 'sandbox' ? ' · Sandbox' : '' }}
                            @elseif($d->enabled())<span class="status-dot warn"></span> Enabled — missing credentials
                            @else<span class="status-dot"></span> Disabled @endif
                        </small>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.integrations.couriers.test', $slug) }}">@csrf
                    <button class="btn btn-sm btn-light"><i class="fa-solid fa-plug-circle-check"></i> Test connection</button>
                </form>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.integrations.couriers.update', $slug) }}" autocomplete="off">
                    @csrf
                    <label class="switch-row">
                        <span><strong>Enable {{ $d->label() }}</strong><small>Show it in “Send to courier” on orders</small></span>
                        <input type="checkbox" name="enabled" value="1" class="switch" @checked($d->enabled())>
                    </label>

                    @foreach($d->fields() as $key => $field)
                        @php $type = $field['type'] ?? 'text'; @endphp
                        <div class="form-group">
                            <label>{{ $field['label'] }} @if(! empty($field['required']))<span style="color:var(--danger)">*</span>@endif</label>
                            @if($type === 'select')
                                <select name="{{ $key }}" class="form-control">
                                    @foreach($field['options'] as $v => $l)<option value="{{ $v }}" @selected((string) $d->config($key) === (string) $v)>{{ $l }}</option>@endforeach
                                </select>
                            @elseif($type === 'store')
                                @if($stores)
                                    <select name="{{ $key }}" class="form-control">
                                        @foreach($stores as $st)<option value="{{ $st['id'] }}" @selected((string) $d->config($key) === (string) $st['id'])>{{ $st['name'] }} (#{{ $st['id'] }})</option>@endforeach
                                    </select>
                                @else
                                    <input name="{{ $key }}" class="form-control" value="{{ $d->config($key) }}" placeholder="Store ID">
                                @endif
                            @elseif(! empty($field['secret']))
                                <input type="password" name="{{ $key }}" class="form-control" autocomplete="new-password"
                                       placeholder="{{ $d->config($key) ? '•••••••• saved — leave blank to keep' : 'Enter ' . strtolower($field['label']) }}">
                            @else
                                <input name="{{ $key }}" class="form-control" value="{{ $d->config($key) }}">
                            @endif
                            @if(! empty($field['hint']))<div class="hint">{{ $field['hint'] }}</div>@endif
                        </div>
                    @endforeach

                    <div class="webhook-box">
                        <small>Webhook URL (paste in the {{ $d->label() }} merchant panel for live status updates)</small>
                        <code>{{ route('webhooks.courier', [$slug, setting('courier_webhook_token')]) }}</code>
                    </div>
                    <button class="btn" style="width:100%;justify-content:center;margin-top:14px"><i class="fa-solid fa-floppy-disk"></i> Save {{ $d->label() }}</button>
                </form>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header"><h3><i class="fa-solid fa-circle-info" style="color:var(--gold)"></i> Where to get API credentials</h3></div>
    <div class="card-body help-grid">
        <div><strong>Steadfast</strong><p>Log in to <a href="https://steadfast.com.bd" target="_blank" rel="noopener">steadfast.com.bd</a> → API → copy the <em>API Key</em> and <em>Secret Key</em>. Set the webhook URL there for automatic delivery updates.</p></div>
        <div><strong>Pathao</strong><p>Log in to <a href="https://merchant.pathao.com/courier/developer-api" target="_blank" rel="noopener">merchant.pathao.com → Developer API</a> → copy Client ID & Client Secret. Username/password are your merchant login. Use <em>Sandbox</em> to test first.</p></div>
        <div><strong>CarryBee</strong><p>Request API access from CarryBee support. You will receive a Client ID, Client Secret and Client Context. Save, then click <em>Test connection</em> to load your pickup stores.</p></div>
    </div>
</div>
@endsection

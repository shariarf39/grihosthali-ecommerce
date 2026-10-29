@extends('admin.layouts.app')

@section('title', 'Email (SMTP)')
@section('subtitle', 'Send order confirmations and alerts from your Gmail or any SMTP account')

@section('content')
@php
    $statuses = json_decode((string) setting('mail_status_list'), true) ?: \App\Services\Notifier::DEFAULT_STATUSES;
    $events = \App\Services\Notifier::EVENTS;
@endphp
<div class="stats" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat"><div class="icon {{ setting('mail_enabled') ? 'green' : 'red' }}"><i class="fa-solid fa-power-off"></i></div><div><small>Email sending</small><strong>{{ setting('mail_enabled') ? 'ON' : 'OFF' }}</strong><small>{{ setting('mail_host') ?: 'SMTP not configured' }}</small></div></div>
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-paper-plane"></i></div><div><small>Sent (30 days)</small><strong>{{ $stats['sent'] }}</strong></div></div>
    <div class="stat"><div class="icon {{ $stats['failed'] ? 'red' : 'gold' }}"><i class="fa-solid fa-triangle-exclamation"></i></div><div><small>Failed (30 days)</small><strong>{{ $stats['failed'] }}</strong><small><a href="{{ route('admin.integrations.email.logs', ['status' => 'failed']) }}">View log</a></small></div></div>
</div>

<form method="POST" action="{{ route('admin.integrations.email.update') }}" autocomplete="off">
    @csrf
    <div class="grid-2">
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-server" style="color:var(--gold)"></i> SMTP Server</h3>
                    <button type="button" class="btn btn-sm btn-light" id="gmail-preset"><i class="fa-brands fa-google"></i> Use Gmail settings</button>
                </div>
                <div class="card-body">
                    <label class="switch-row">
                        <span><strong>Send emails</strong><small>Master switch — turn off to stop all emails</small></span>
                        <input type="checkbox" name="mail_enabled" value="1" class="switch" @checked(setting('mail_enabled'))>
                    </label>
                    <div class="form-row-3">
                        <div class="form-group" style="grid-column:span 2"><label>SMTP Host</label><input name="mail_host" id="mail_host" class="form-control" value="{{ setting('mail_host') }}" placeholder="smtp.gmail.com"></div>
                        <div class="form-group"><label>Port</label><input name="mail_port" id="mail_port" type="number" class="form-control" value="{{ setting('mail_port', 587) }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Encryption</label>
                            <select name="mail_encryption" id="mail_encryption" class="form-control">
                                @foreach(['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)', 'none' => 'None'] as $k => $l)<option value="{{ $k }}" @selected(setting('mail_encryption', 'tls') === $k)>{{ $l }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Username (email)</label><input name="mail_username" class="form-control" value="{{ setting('mail_username') }}" placeholder="yourshop@gmail.com"></div>
                    </div>
                    <div class="form-group"><label>Password / App Password</label>
                        <input type="password" name="mail_password" class="form-control" autocomplete="new-password" placeholder="{{ setting('mail_password') ? '•••••••• saved — leave blank to keep' : '16-character Gmail App Password' }}">
                        <div class="hint">For Gmail, turn on 2-Step Verification, then create an <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">App Password</a>. Your normal Gmail password will not work.</div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>From Email</label><input name="mail_from_address" class="form-control" value="{{ setting('mail_from_address') }}" placeholder="yourshop@gmail.com"></div>
                        <div class="form-group"><label>From Name</label><input name="mail_from_name" class="form-control" value="{{ setting('mail_from_name', setting('site_name')) }}"></div>
                    </div>
                    <div class="form-group"><label>Admin alert recipients</label><input name="mail_admin_recipients" class="form-control" value="{{ setting('mail_admin_recipients') }}" placeholder="owner@gmail.com, manager@gmail.com"><div class="hint">Comma-separated. New order, return and contact alerts go here.</div></div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-toggle-on" style="color:var(--gold)"></i> Which emails to send</h3></div>
                <div class="card-body">
                    @foreach(['Customer', 'Admin'] as $aud)
                        <h4 class="settings-section-title">{{ $aud }} emails</h4>
                        @foreach($events as $key => [$label, $audience, $default])
                            @continue($audience !== $aud)
                            <label class="switch-row compact">
                                <span>{{ $label }}</span>
                                <input type="checkbox" name="events[{{ $key }}]" value="1" class="switch" @checked(setting("mail_event_{$key}", $default ? '1' : '0'))>
                            </label>
                        @endforeach
                    @endforeach
                    <h4 class="settings-section-title">Send status emails when order becomes</h4>
                    <div class="tag-checks">
                        @foreach(\App\Models\Order::STATUSES as $k => $l)
                            @continue($k === 'pending')
                            <label><input type="checkbox" name="mail_status_list[]" value="{{ $k }}" @checked(in_array($k, $statuses))> {{ $l }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
            <button class="btn" style="width:100%;justify-content:center;height:46px"><i class="fa-solid fa-floppy-disk"></i> Save Email Settings</button>
        </div>
    </div>
</form>

<div class="grid-2" style="margin-top:22px">
    <div class="card">
        <div class="card-header"><h3>Recent emails</h3><a href="{{ route('admin.integrations.email.logs') }}" class="btn btn-sm btn-light">Full log</a></div>
        <div class="table-wrap">
            <table class="table">
                @forelse($logs as $log)
                    <tr>
                        <td><span class="badge badge-{{ $log->status === 'sent' ? 'success' : 'danger' }}">{{ ucfirst($log->status) }}</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($log->subject, 50) }}<div class="muted">{{ $log->to }}</div></td>
                        <td class="muted">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td class="empty">No emails sent yet.</td></tr>
                @endforelse
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fa-solid fa-vial" style="color:var(--gold)"></i> Send a test email</h3></div>
        <div class="card-body">
            <p class="muted" style="margin-top:0">Save your settings first, then send a test to make sure everything works.</p>
            <form method="POST" action="{{ route('admin.integrations.email.test') }}" class="filters">
                @csrf
                <input type="email" name="test_to" class="form-control" value="{{ old('test_to', auth()->user()->email) }}" required style="flex:1">
                <button class="btn"><i class="fa-solid fa-paper-plane"></i> Send test</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('gmail-preset').addEventListener('click', () => {
        document.getElementById('mail_host').value = 'smtp.gmail.com';
        document.getElementById('mail_port').value = '587';
        document.getElementById('mail_encryption').value = 'tls';
    });
</script>
@endpush

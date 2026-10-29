@extends('emails.layout')

@section('body')
    <h2 style="margin:0 0 6px;color:#222;">✅ Email is working!</h2>
    <p>This test email confirms that the SMTP settings in your {{ setting('site_name') }} admin panel are correct. Order confirmations and alerts will now be delivered.</p>
    <p style="color:#888;font-size:13px;">Sent {{ now()->format('d M Y, h:i A') }} via {{ setting('mail_host') }}</p>
@endsection

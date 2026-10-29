@extends('emails.layout')

@section('body')
    <h2 style="margin:0 0 6px;color:#222;">New contact message</h2>
    <p><strong>{{ $message->name }}</strong> {{ $message->phone }} {{ $message->email }}<br><strong>Subject:</strong> {{ $message->subject ?: '—' }}</p>
    <p style="background:#f6f3ea;padding:12px 14px;border-radius:8px;white-space:pre-line;">{{ $message->message }}</p>
    <p style="text-align:center;"><a href="{{ route('admin.messages.show', $message) }}" style="background:#222;color:#fff;padding:11px 24px;border-radius:6px;text-decoration:none;display:inline-block;">Open in admin</a></p>
@endsection

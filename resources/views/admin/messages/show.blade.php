@extends('admin.layouts.app')

@section('title', 'Message')

@section('content')
<div class="card" style="max-width:760px">
    <div class="card-header"><h3>{{ $message->subject ?: 'No subject' }}</h3><span class="muted">{{ $message->created_at->format('d M Y, h:i A') }}</span></div>
    <div class="card-body">
        <ul class="info-list" style="margin-bottom:18px">
            <li><span>Name</span><span>{{ $message->name }}</span></li>
            @if($message->phone)<li><span>Phone</span><span><a href="tel:{{ $message->phone }}">{{ $message->phone }}</a></span></li>@endif
            @if($message->email)<li><span>Email</span><span><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></span></li>@endif
        </ul>
        <p style="white-space:pre-line;color:var(--title)">{{ $message->message }}</p>
        <div class="actions" style="margin-top:20px">
            <a href="{{ route('admin.messages.index') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i> Back</a>
            @if($message->email)<a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}" class="btn"><i class="fa-solid fa-reply"></i> Reply</a>@endif
        </div>
    </div>
</div>
@endsection

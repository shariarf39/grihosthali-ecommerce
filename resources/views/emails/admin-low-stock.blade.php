@extends('emails.layout')

@section('body')
    <h2 style="margin:0 0 6px;color:#222;">Low stock alert</h2>
    <p><strong>{{ $product->name }}</strong> has only <strong style="color:#c62828;">{{ $product->stock }}</strong> unit(s) left (alert level {{ $product->low_stock_alert }}).</p>
    <p style="text-align:center;"><a href="{{ route('admin.inventory.index', ['q' => $product->name]) }}" style="background:#222;color:#fff;padding:11px 24px;border-radius:6px;text-decoration:none;display:inline-block;">Restock now</a></p>
@endsection

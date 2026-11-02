@extends('errors.minimal-brand')

@section('title', 'Page not found')
@section('code', '404')
@section('heading', 'This page could not be found')
@section('message', 'The link may be old or the product may no longer be available. Try our shop instead.')
@section('extra')
    <a href="{{ url('/shop') }}" class="btn ghost">Browse products</a>
@endsection

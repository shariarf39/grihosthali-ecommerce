@extends('errors.minimal-brand')

@section('title', 'Something went wrong')
@section('code', '500')
@section('heading', 'Something went wrong on our side')
@section('message', 'Sorry for the trouble — please try again in a moment. Your cart is safe.')
@section('extra')
    <a href="javascript:location.reload()" class="btn ghost">Try again</a>
@endsection

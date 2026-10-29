@extends('layouts.app')

@section('title', $page->title)

@section('content')
@include('partials.breadcrumb', ['items' => [$page->title => null]])

<div class="container">
    <div class="page-content">
        <h1 class="page-title">{{ $page->title }}</h1>
        <div class="rich-content">{!! $page->content !!}</div>
    </div>
</div>
@endsection

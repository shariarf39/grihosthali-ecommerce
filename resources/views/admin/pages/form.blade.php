@extends('admin.layouts.app')

@section('title', $page->exists ? 'Edit Page' : 'Add Page')

@section('content')
<form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
    @csrf
    @if($page->exists) @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group"><label>Title *</label><input name="title" class="form-control" value="{{ old('title', $page->title) }}" required></div>
                <div class="form-group"><label>Slug</label><input name="slug" class="form-control" value="{{ old('slug', $page->slug) }}" placeholder="auto-generated"></div>
            </div>
            <div class="form-group"><label>Content</label>@include('admin.partials.editor', ['name' => 'content', 'value' => old('content', $page->content)])</div>
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->is_active))> Published</label>
            <button class="btn"><i class="fa-solid fa-floppy-disk"></i> Save Page</button>
        </div>
    </div>
</form>
@endsection

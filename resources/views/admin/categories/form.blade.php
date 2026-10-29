@extends('admin.layouts.app')

@section('title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
    @csrf
    @if($category->exists) @method('PUT') @endif
    <div class="form-grid">
        <div class="card">
            <div class="card-body">
                <div class="form-group"><label>Name *</label><input name="name" class="form-control" value="{{ old('name', $category->name) }}" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Slug</label><input name="slug" class="form-control" value="{{ old('slug', $category->slug) }}" placeholder="auto-generated"></div>
                    <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}"></div>
                </div>
                <div class="form-group"><label>Parent Category</label>
                    <select name="parent_id" class="form-control">
                        <option value="">— Top level —</option>
                        @foreach($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))> Active</label>
                <label class="check"><input type="checkbox" name="show_in_menu" value="1" @checked(old('show_in_menu', $category->show_in_menu))> Show in header menu</label>
                <label class="check"><input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $category->show_on_home))> Show as a product section on the homepage</label>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label>Image</label>
                    <img id="cat-preview" src="{{ $category->image ? $category->image_url : '' }}" class="img-preview" style="{{ $category->image ? '' : 'display:none' }}" alt="">
                    <input type="file" name="image" accept="image/*" class="form-control" style="padding:8px" data-preview="#cat-preview">
                    <div class="hint">Square image (300×300) works best.</div>
                </div>
                <button class="btn" style="width:100%;justify-content:center"><i class="fa-solid fa-floppy-disk"></i> Save Category</button>
            </div>
        </div>
    </div>
</form>
@endsection

@extends('admin.layouts.app')

@section('title', $brand->exists ? 'Edit Brand' : 'Add Brand')

@section('content')
<div class="card" style="max-width:640px">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}">
            @csrf
            @if($brand->exists) @method('PUT') @endif
            <div class="form-group"><label>Name *</label><input name="name" class="form-control" value="{{ old('name', $brand->name) }}" required></div>
            <div class="form-group"><label>Slug</label><input name="slug" class="form-control" value="{{ old('slug', $brand->slug) }}" placeholder="auto-generated"></div>
            <div class="form-group">
                <label>Logo</label>
                <img id="brand-preview" src="{{ $brand->logo ? $brand->logo_url : '' }}" class="img-preview" style="{{ $brand->logo ? '' : 'display:none' }}" alt="">
                <input type="file" name="logo" accept="image/*" class="form-control" style="padding:8px" data-preview="#brand-preview">
            </div>
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $brand->is_active))> Active</label>
            <button class="btn"><i class="fa-solid fa-floppy-disk"></i> Save Brand</button>
        </form>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', $slider->exists ? 'Edit Slide' : 'Add Slide')

@section('content')
<div class="card" style="max-width:820px">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="{{ $slider->exists ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}">
            @csrf
            @if($slider->exists) @method('PUT') @endif
            <div class="form-group">
                <label>Banner Image {{ $slider->exists ? '' : '*' }}</label>
                <img id="slide-preview" src="{{ $slider->image ? $slider->image_url : '' }}" class="img-preview" style="{{ $slider->image ? '' : 'display:none' }}" alt="">
                <input type="file" name="image" accept="image/*" class="form-control" style="padding:8px" data-preview="#slide-preview">
                <div class="hint">Recommended size 1280×480 px. If the image already contains text, leave the title empty.</div>
            </div>
            <div class="form-group"><label>Title (overlay text)</label><input name="title" class="form-control" value="{{ old('title', $slider->title) }}"></div>
            <div class="form-group"><label>Subtitle</label><input name="subtitle" class="form-control" value="{{ old('subtitle', $slider->subtitle) }}"></div>
            <div class="form-row">
                <div class="form-group"><label>Button Text</label><input name="button_text" class="form-control" value="{{ old('button_text', $slider->button_text) }}" placeholder="Shop Now"></div>
                <div class="form-group"><label>Link URL</label><input name="link" class="form-control" value="{{ old('link', $slider->link) }}" placeholder="/category/honey or https://..."></div>
            </div>
            <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $slider->sort_order ?? 0) }}"></div>
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slider->is_active))> Active</label>
            <button class="btn"><i class="fa-solid fa-floppy-disk"></i> Save Slide</button>
        </form>
    </div>
</div>
@endsection

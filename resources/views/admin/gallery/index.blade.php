@extends('admin.layouts.app')

@section('title', 'Showcase Gallery')
@section('subtitle', 'The rotating 3D image gallery on the homepage')

@section('content')
<div class="grid-2">
    <div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-images" style="color:var(--gold)"></i> Images ({{ $images->count() }})</h3>
                <span class="muted">Portrait photos (4:5, e.g. 800×1000) look best</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="upload-drop">
                    @csrf
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <div><strong>Add images</strong><small>Select one or many (up to 20 at a time)</small></div>
                    <input type="file" name="images[]" accept="image/*" multiple required class="form-control" style="padding:8px;max-width:260px">
                    <button class="btn"><i class="fa-solid fa-plus"></i> Upload</button>
                </form>

                <div class="gallery-admin-grid">
                    @forelse($images as $img)
                        <form method="POST" action="{{ route('admin.gallery.update', $img) }}" enctype="multipart/form-data" class="gallery-item {{ $img->is_active ? '' : 'off' }}">
                            @csrf @method('PUT')
                            <div class="gi-thumb">
                                <img src="{{ $img->image_url }}" alt="" id="gi-{{ $img->id }}">
                                <label class="gi-replace" title="Replace image"><i class="fa-solid fa-camera"></i>
                                    <input type="file" name="image" accept="image/*" hidden data-preview="#gi-{{ $img->id }}">
                                </label>
                            </div>
                            <input name="caption" class="form-control" value="{{ $img->caption }}" placeholder="Caption (optional)">
                            <input name="link" class="form-control" value="{{ $img->link }}" placeholder="Link, e.g. /category/honey">
                            <div class="gi-row">
                                <input type="number" name="sort_order" class="form-control" value="{{ $img->sort_order }}" title="Order" style="width:70px">
                                <label class="check" style="margin:0"><input type="checkbox" name="is_active" value="1" @checked($img->is_active)> Show</label>
                                <button class="btn btn-sm" title="Save"><i class="fa-solid fa-check"></i></button>
                                <button type="submit" form="del-{{ $img->id }}" class="btn btn-sm btn-danger btn-icon" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
                            </div>
                        </form>
                        <form id="del-{{ $img->id }}" method="POST" action="{{ route('admin.gallery.destroy', $img) }}" data-confirm="Remove this image from the gallery?" hidden>@csrf @method('DELETE')</form>
                    @empty
                        <p class="empty" style="grid-column:1/-1">No images yet — upload a few above.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header"><h3>Section text</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.gallery.settings') }}">
                    @csrf
                    <label class="switch-row">
                        <span><strong>Show on homepage</strong><small>Turn the whole gallery section on or off</small></span>
                        <input type="checkbox" name="home_show_gallery" value="1" class="switch" @checked(setting('home_show_gallery'))>
                    </label>
                    <div class="form-group"><label>Small heading</label><input name="gallery_eyebrow" class="form-control" value="{{ setting('gallery_eyebrow') }}"></div>
                    <div class="form-group"><label>Title</label><input name="gallery_title" class="form-control" value="{{ setting('gallery_title') }}"></div>
                    <div class="form-group"><label>Subtitle</label><input name="gallery_subtitle" class="form-control" value="{{ setting('gallery_subtitle') }}"></div>
                    <div class="form-row">
                        <div class="form-group"><label>Button text</label><input name="gallery_button_text" class="form-control" value="{{ setting('gallery_button_text') }}"></div>
                        <div class="form-group"><label>Button link</label><input name="gallery_button_link" class="form-control" value="{{ setting('gallery_button_link', setting('facebook')) }}" placeholder="https://facebook.com/..."></div>
                    </div>
                    <div class="form-group"><label>Rotation speed</label>
                        <select name="gallery_speed" class="form-control">
                            @foreach(['2200' => 'Fast', '3200' => 'Normal', '4500' => 'Slow', '6500' => 'Very slow'] as $ms => $l)
                                <option value="{{ $ms }}" @selected((string) setting('gallery_speed') === (string) $ms)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn" style="width:100%;justify-content:center"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

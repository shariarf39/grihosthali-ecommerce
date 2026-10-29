@extends('admin.layouts.app')

@section('title', $testimonial->exists ? 'Edit Testimonial' : 'Add Testimonial')

@section('content')
<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
            @csrf
            @if($testimonial->exists) @method('PUT') @endif
            <div class="form-row">
                <div class="form-group"><label>Name *</label><input name="name" class="form-control" value="{{ old('name', $testimonial->name) }}" required></div>
                <div class="form-group"><label>Designation / Location</label><input name="designation" class="form-control" value="{{ old('designation', $testimonial->designation) }}"></div>
            </div>
            <div class="form-group"><label>Message *</label><textarea name="message" rows="4" class="form-control" required>{{ old('message', $testimonial->message) }}</textarea></div>
            <div class="form-row">
                <div class="form-group"><label>Rating</label>
                    <select name="rating" class="form-control">@for($i = 5; $i >= 1; $i--)<option value="{{ $i }}" @selected(old('rating', $testimonial->rating) == $i)>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>@endfor</select>
                </div>
                <div class="form-group"><label>Photo</label><input type="file" name="image" accept="image/*" class="form-control" style="padding:8px"></div>
            </div>
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->is_active))> Active</label>
            <button class="btn"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        </form>
    </div>
</div>
@endsection

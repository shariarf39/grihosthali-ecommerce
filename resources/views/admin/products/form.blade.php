@extends('admin.layouts.app')

@section('title', $product->exists ? 'Edit Product' : 'Add Product')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if($product->exists) @method('PUT') @endif
    <div class="form-grid">
        <div>
            <div class="card">
                <div class="card-header"><h3>Product Information</h3></div>
                <div class="card-body">
                    <div class="form-group"><label>Product Name *</label><input name="name" class="form-control" value="{{ old('name', $product->name) }}" required></div>
                    <div class="form-row">
                        <div class="form-group"><label>Slug (URL)</label><input name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" placeholder="auto-generated"></div>
                        <div class="form-group"><label>SKU</label><input name="sku" class="form-control" value="{{ old('sku', $product->sku) }}"></div>
                    </div>
                    <div class="form-group"><label>Short Description</label><textarea name="short_description" rows="3" class="form-control">{{ old('short_description', $product->short_description) }}</textarea></div>
                    <div class="form-group"><label>Full Description</label>@include('admin.partials.editor', ['name' => 'description', 'value' => old('description', $product->description)])</div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3>Pricing & Inventory</h3></div>
                <div class="card-body">
                    <div class="form-row-3">
                        <div class="form-group"><label>Regular Price (৳) *</label><input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required></div>
                        <div class="form-group"><label>Discount Price (৳)</label><input type="number" step="0.01" name="discount_price" class="form-control" value="{{ old('discount_price', $product->discount_price) }}"><div class="hint">Leave empty for no discount</div></div>
                        <div class="form-group"><label>Stock *</label><input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required></div>
                    </div>
                    <div class="form-group"><label>Unit / Size</label><input name="unit" class="form-control" value="{{ old('unit', $product->unit) }}" placeholder="e.g. 500 ml, 1 kg"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3>SEO</h3></div>
                <div class="card-body">
                    <div class="form-group"><label>Meta Title</label><input name="meta_title" class="form-control" value="{{ old('meta_title', $product->meta_title) }}"></div>
                    <div class="form-group"><label>Meta Description</label><textarea name="meta_description" rows="2" class="form-control">{{ old('meta_description', $product->meta_description) }}</textarea></div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><h3>Publish</h3></div>
                <div class="card-body">
                    <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Active (visible in store)</label>
                    <label class="check"><input type="checkbox" name="is_top_selling" value="1" @checked(old('is_top_selling', $product->is_top_selling))> Show in “Top Selling”</label>
                    <div class="form-group"><label>Badge / Flag</label>
                        <select name="flag" class="form-control">
                            <option value="">None</option>
                            @foreach($flags as $f)<option @selected(old('flag', $product->flag) === $f)>{{ $f }}</option>@endforeach
                        </select>
                    </div>
                    <button class="btn" style="width:100%;justify-content:center"><i class="fa-solid fa-floppy-disk"></i> {{ $product->exists ? 'Update Product' : 'Save Product' }}</button>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3>Organization</h3></div>
                <div class="card-body">
                    <div class="form-group"><label>Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">— None —</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->parent ? $c->parent->name . ' › ' : '' }}{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Brand</label>
                        <select name="brand_id" class="form-control">
                            <option value="">— None —</option>
                            @foreach($brands as $b)<option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id) == $b->id)>{{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3>Images</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Main Image {{ $product->exists ? '' : '*' }}</label>
                        <img id="main-preview" src="{{ $product->image ? $product->image_url : '' }}" class="img-preview" style="{{ $product->image ? '' : 'display:none' }}" alt="">
                        <input type="file" name="image" accept="image/*" class="form-control" style="padding:8px" data-preview="#main-preview">
                        <div class="hint">Square image recommended (800×800)</div>
                    </div>
                    <div class="form-group">
                        <label>Gallery Images</label>
                        @if($product->gallery)
                            <div class="gallery-list">
                                @foreach($product->gallery as $img)
                                    <label title="Tick to remove"><img src="{{ upload_url($img) }}" alt=""><input type="checkbox" name="remove_gallery[]" value="{{ $img }}"></label>
                                @endforeach
                            </div>
                            <div class="hint" style="margin-bottom:8px">Tick an image to remove it.</div>
                        @endif
                        <input type="file" name="gallery[]" accept="image/*" multiple class="form-control" style="padding:8px">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

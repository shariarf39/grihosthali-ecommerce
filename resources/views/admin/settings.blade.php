@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
@php
    $img = function ($key, $label, $hint = '') {
        $val = setting($key);
        return compact('key', 'label', 'hint', 'val');
    };
@endphp
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="tabs">
            <button type="button" class="active" data-tab="t-general">General</button>
            <button type="button" data-tab="t-contact">Contact & Social</button>
            <button type="button" data-tab="t-shipping">Delivery</button>
            <button type="button" data-tab="t-appearance">Logo & Colors</button>
            <button type="button" data-tab="t-seo">SEO</button>
        </div>
        <div class="card-body">
            <div class="tab-pane active" id="t-general">
                <div class="form-row">
                    <div class="form-group"><label>Site Name *</label><input name="site_name" class="form-control" value="{{ setting('site_name') }}" required></div>
                    <div class="form-group"><label>Tagline</label><input name="site_tagline" class="form-control" value="{{ setting('site_tagline') }}"></div>
                </div>
                <div class="form-group"><label>Top Notice Bar</label><input name="top_notice" class="form-control" value="{{ setting('top_notice') }}"><div class="hint">Shown above the header. Leave empty to hide.</div></div>
                <div class="form-group"><label>Footer About Text</label><textarea name="footer_about" rows="3" class="form-control">{{ setting('footer_about') }}</textarea></div>
                <div class="form-group"><label>Copyright Text</label><input name="copyright" class="form-control" value="{{ setting('copyright') }}"></div>
            </div>

            <div class="tab-pane" id="t-contact">
                <div class="form-row">
                    <div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ setting('phone') }}"></div>
                    <div class="form-group"><label>Hotline (Call for order)</label><input name="hotline" class="form-control" value="{{ setting('hotline') }}"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Email</label><input name="email" type="email" class="form-control" value="{{ setting('email') }}"></div>
                    <div class="form-group"><label>WhatsApp Number</label><input name="whatsapp" class="form-control" value="{{ setting('whatsapp') }}" placeholder="8801XXXXXXXXX"></div>
                </div>
                <div class="form-group"><label>Address</label><input name="address" class="form-control" value="{{ setting('address') }}"></div>
                <div class="form-row">
                    <div class="form-group"><label>Facebook URL</label><input name="facebook" class="form-control" value="{{ setting('facebook') }}"></div>
                    <div class="form-group"><label>Instagram URL</label><input name="instagram" class="form-control" value="{{ setting('instagram') }}"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>YouTube URL</label><input name="youtube" class="form-control" value="{{ setting('youtube') }}"></div>
                    <div class="form-group"><label>TikTok URL</label><input name="tiktok" class="form-control" value="{{ setting('tiktok') }}"></div>
                </div>
                <div class="form-group"><label>Messenger URL</label><input name="messenger" class="form-control" value="{{ setting('messenger') }}"></div>
            </div>

            <div class="tab-pane" id="t-shipping">
                <div class="form-row-3">
                    <div class="form-group"><label>Inside Dhaka Charge (৳) *</label><input type="number" name="shipping_inside" class="form-control" value="{{ setting('shipping_inside', 70) }}" required></div>
                    <div class="form-group"><label>Outside Dhaka Charge (৳) *</label><input type="number" name="shipping_outside" class="form-control" value="{{ setting('shipping_outside', 130) }}" required></div>
                    <div class="form-group"><label>Free Delivery Above (৳)</label><input type="number" name="free_shipping_min" class="form-control" value="{{ setting('free_shipping_min', 0) }}"><div class="hint">0 = disabled</div></div>
                </div>
            </div>

            <div class="tab-pane" id="t-appearance">
                <div class="form-row">
                    <div class="form-group"><label>Primary Color</label><input type="color" name="primary_color" class="form-control" style="padding:4px" value="{{ setting('primary_color', '#1f4d36') }}"></div>
                    <div class="form-group"><label>Accent Color</label><input type="color" name="secondary_color" class="form-control" style="padding:4px" value="{{ setting('secondary_color', '#c9a24a') }}"></div>
                </div>
                <div class="form-row">
                    @foreach([$img('logo', 'Logo', 'Leave empty to use the Bangla text logo'), $img('favicon', 'Favicon'), $img('payment_banner', 'Payment Methods Banner (footer)')] as $f)
                        <div class="form-group">
                            <label>{{ $f['label'] }}</label>
                            <img id="prev-{{ $f['key'] }}" src="{{ $f['val'] ? upload_url($f['val']) : '' }}" class="img-preview" style="max-height:90px;{{ $f['val'] ? '' : 'display:none' }}" alt="">
                            <input type="file" name="{{ $f['key'] }}" accept="image/*" class="form-control" style="padding:8px" data-preview="#prev-{{ $f['key'] }}">
                            @if($f['hint'])<div class="hint">{{ $f['hint'] }}</div>@endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tab-pane" id="t-seo">
                <div class="form-group"><label>Meta Description</label><textarea name="meta_description" rows="3" class="form-control">{{ setting('meta_description') }}</textarea></div>
                <div class="form-group"><label>Meta Keywords</label><input name="meta_keywords" class="form-control" value="{{ setting('meta_keywords') }}"></div>
            </div>

            <button class="btn"><i class="fa-solid fa-floppy-disk"></i> Save Settings</button>
        </div>
    </div>
</form>
@endsection

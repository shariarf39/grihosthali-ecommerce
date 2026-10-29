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
    <input type="hidden" name="_has_toggles" value="1">
    <div class="card">
        <div class="tabs">
            <button type="button" class="active" data-tab="t-general">General</button>
            <button type="button" data-tab="t-contact">Contact & Social</button>
            <button type="button" data-tab="t-home">Homepage</button>
            <button type="button" data-tab="t-shipping">Delivery & Checkout</button>
            <button type="button" data-tab="t-returns">Returns & CRM</button>
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
                <div class="form-row">
                    <div class="form-group"><label>Copyright Text</label><input name="copyright" class="form-control" value="{{ setting('copyright') }}" placeholder="© {{ date('Y') }} Grihosthali. All rights reserved."></div>
                    <div class="form-group"><label>Footer Tagline</label><input name="footer_tagline" class="form-control" value="{{ setting('footer_tagline') }}"></div>
                </div>
            </div>

            <div class="tab-pane" id="t-home">
                <h4 class="settings-section-title">Show / hide homepage sections</h4>
                <div class="section-toggle">
                    @foreach(['home_show_features' => 'Feature strip (delivery, COD…)', 'home_show_categories' => 'Featured categories', 'home_show_top_selling' => 'Top selling products', 'home_show_promo' => 'Promo banner', 'home_show_brands' => 'Our brands', 'home_show_just_for_you' => 'Just for you grid', 'home_show_testimonials' => 'Testimonials'] as $k => $l)
                        <label class="check"><input type="checkbox" name="{{ $k }}" value="1" @checked(setting($k))> {{ $l }}</label>
                    @endforeach
                </div>
                <p class="hint">Category product sections are controlled per category (Categories → “Show as a product section on the homepage”). Hero slides are managed under Sliders.</p>

                <h4 class="settings-section-title">Feature strip (below slider)</h4>
                @for($i = 1; $i <= 4; $i++)
                    <div class="feature-row">
                        <input name="feature{{ $i }}_icon" class="form-control" value="{{ setting('feature' . $i . '_icon') }}" placeholder="Icon class">
                        <input name="feature{{ $i }}_title" class="form-control" value="{{ setting('feature' . $i . '_title') }}" placeholder="Title">
                        <input name="feature{{ $i }}_text" class="form-control" value="{{ setting('feature' . $i . '_text') }}" placeholder="Small text">
                    </div>
                @endfor
                <p class="hint">Icons use Font Awesome class names, e.g. <code>fa-solid fa-truck-fast</code>, <code>fa-solid fa-leaf</code>, <code>fa-solid fa-gift</code> — browse at fontawesome.com/icons.</p>

                <h4 class="settings-section-title">Section headings</h4>
                <div class="form-row">
                    <div class="form-group"><label>Categories Title</label><input name="categories_title" class="form-control" value="{{ setting('categories_title') }}"></div>
                    <div class="form-group"><label>Categories Subtitle</label><input name="categories_subtitle" class="form-control" value="{{ setting('categories_subtitle') }}"></div>
                </div>
                <div class="form-row-3">
                    <div class="form-group"><label>Top Selling Title</label><input name="top_selling_title" class="form-control" value="{{ setting('top_selling_title') }}"></div>
                    <div class="form-group"><label>“Just For You” Title</label><input name="just_for_you_title" class="form-control" value="{{ setting('just_for_you_title') }}"></div>
                    <div class="form-group"><label>“Just For You” Product Count</label><input type="number" name="just_for_you_count" class="form-control" value="{{ setting('just_for_you_count') }}"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Testimonials Title</label><input name="testimonials_title" class="form-control" value="{{ setting('testimonials_title') }}"></div>
                    <div class="form-group"><label>Testimonials Subtitle</label><input name="testimonials_subtitle" class="form-control" value="{{ setting('testimonials_subtitle') }}"></div>
                </div>

                <h4 class="settings-section-title">Promo banner</h4>
                <div class="form-row">
                    <div>
                        <div class="form-group"><label>Small Heading</label><input name="promo_eyebrow" class="form-control" value="{{ setting('promo_eyebrow') }}"></div>
                        <div class="form-group"><label>Title</label><input name="promo_title" class="form-control" value="{{ setting('promo_title') }}"></div>
                        <div class="form-group"><label>Text</label><textarea name="promo_text" rows="2" class="form-control">{{ setting('promo_text') }}</textarea></div>
                        <div class="form-row">
                            <div class="form-group"><label>Button Text</label><input name="promo_button" class="form-control" value="{{ setting('promo_button') }}"></div>
                            <div class="form-group"><label>Button Link</label><input name="promo_link" class="form-control" value="{{ setting('promo_link') }}" placeholder="/category/ghee-butter"></div>
                        </div>
                        <div class="form-group"><label>Show after category section #</label><input type="number" name="promo_after_section" class="form-control" value="{{ setting('promo_after_section') }}"><div class="hint">0 = right after Top Selling</div></div>
                    </div>
                    <div class="form-group">
                        <label>Banner Image</label>
                        <img id="prev-promo" src="{{ setting('promo_image') ? upload_url(setting('promo_image')) : asset('uploads/sliders/ghee-hero.png') }}" class="img-preview" alt="">
                        <input type="file" name="promo_image" accept="image/*" class="form-control" style="padding:8px" data-preview="#prev-promo">
                    </div>
                </div>
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
                <div class="form-row">
                    <div class="form-group"><label>Inside Dhaka Delivery Time</label><input name="delivery_time_inside" class="form-control" value="{{ setting('delivery_time_inside') }}"></div>
                    <div class="form-group"><label>Outside Dhaka Delivery Time</label><input name="delivery_time_outside" class="form-control" value="{{ setting('delivery_time_outside') }}"></div>
                </div>
                <h4 class="settings-section-title">Checkout page</h4>
                <div class="form-group"><label>Checkout Notice</label><textarea name="checkout_notice" rows="2" class="form-control">{{ setting('checkout_notice') }}</textarea></div>
                <div class="form-group"><label>Order Button Text</label><input name="checkout_button" class="form-control" value="{{ setting('checkout_button') }}"></div>
                <div class="form-group"><label>Order Success Message</label><textarea name="order_success_message" rows="2" class="form-control">{{ setting('order_success_message') }}</textarea></div>
            </div>

            <div class="tab-pane" id="t-returns">
                <h4 class="settings-section-title">Return policy</h4>
                <div class="form-row">
                    <div class="form-group"><label>Return Window (days after delivery)</label><input type="number" name="return_days" class="form-control" value="{{ setting('return_days') }}"><div class="hint">0 disables online return requests</div></div>
                    <div class="form-group"><label>Return Note (shown to customers)</label><input name="return_policy_note" class="form-control" value="{{ setting('return_policy_note') }}"></div>
                </div>
                <h4 class="settings-section-title">CRM segments</h4>
                <div class="form-row">
                    <div class="form-group"><label>VIP when lifetime spend reaches (৳)</label><input type="number" name="crm_vip_amount" class="form-control" value="{{ setting('crm_vip_amount') }}"></div>
                    <div class="form-group"><label>“At Risk” after no order for (days)</label><input type="number" name="crm_inactive_days" class="form-control" value="{{ setting('crm_inactive_days') }}"></div>
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Fallback values for every admin-editable setting, so the storefront never shows blanks. */
    public const DEFAULTS = [
        'site_name' => 'Grihosthali',
        'site_tagline' => 'Pure • Natural • Traditional',
        'shipping_inside' => '70',
        'shipping_outside' => '130',
        'free_shipping_min' => '0',
        'delivery_time_inside' => '1–2 days',
        'delivery_time_outside' => '2–4 days',
        'return_days' => '7',
        'crm_vip_amount' => '10000',
        'crm_inactive_days' => '90',
        'primary_color' => '#1f4d36',
        'secondary_color' => '#c9a24a',

        // Homepage sections (1 = shown)
        'home_show_features' => '1',
        'home_show_categories' => '1',
        'home_show_top_selling' => '1',
        'home_show_promo' => '1',
        'home_show_brands' => '1',
        'home_show_just_for_you' => '1',
        'home_show_testimonials' => '1',
        'feature1_icon' => 'fa-solid fa-truck-fast', 'feature1_title' => 'Fast Delivery', 'feature1_text' => 'All over Bangladesh',
        'feature2_icon' => 'fa-solid fa-hand-holding-dollar', 'feature2_title' => 'Cash on Delivery', 'feature2_text' => 'Pay when you receive',
        'feature3_icon' => 'fa-solid fa-leaf', 'feature3_title' => '100% Natural', 'feature3_text' => 'Pure & traditional',
        'feature4_icon' => 'fa-solid fa-headset', 'feature4_title' => 'Customer Support', 'feature4_text' => '10am – 10pm, every day',
        'categories_title' => 'Featured Categories',
        'categories_subtitle' => 'Get your desired product from featured category!',
        'top_selling_title' => 'Top Selling Products',
        'just_for_you_title' => 'Just For You',
        'just_for_you_count' => '15',
        'testimonials_title' => 'What Our Customers Say',
        'testimonials_subtitle' => 'Thousands of happy families trust Grihosthali every day',
        'promo_eyebrow' => 'Pure • Natural • Traditional',
        'promo_title' => 'Premium Desi Ghee',
        'promo_text' => 'খাঁটি স্বাদ, সবার রান্নায় — গ্রামের ঐতিহ্যবাহী পদ্ধতিতে তৈরি বিশুদ্ধ গাওয়া ঘি।',
        'promo_button' => 'Order Now',
        'promo_link' => '/products/premium-desi-ghee-500ml',
        'promo_after_section' => '2',

        'checkout_notice' => 'অর্ডার কনফার্ম করতে আপনার নাম, ঠিকানা ও মোবাইল নাম্বার লিখে “অর্ডার কনফার্ম করুন” বাটনে ক্লিক করুন।',
        'checkout_button' => 'অর্ডার কনফার্ম করুন',
        'order_success_message' => 'আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে। আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে যোগাযোগ করবেন।',
        'return_policy_note' => 'ডেলিভারির পর নির্দিষ্ট সময়ের মধ্যে ক্ষতিগ্রস্ত, ভুল বা মানহীন পণ্য ফেরত দিতে পারবেন।',
        'footer_tagline' => 'খাঁটি স্বাদ, সবার রান্নায়',
    ];

    public static function cached(): array
    {
        return Cache::rememberForever('site_settings', function () {
            return Schema::hasTable('settings') ? self::pluck('value', 'key')->toArray() : [];
        });
    }

    public static function get(string $key, $default = null)
    {
        $value = self::cached()[$key] ?? null;

        return ($value === null || $value === '') ? ($default ?? self::DEFAULTS[$key] ?? null) : $value;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('site_settings');
    }
}

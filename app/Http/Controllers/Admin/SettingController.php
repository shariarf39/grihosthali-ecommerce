<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /** Text settings editable from the admin panel. */
    private const TEXT_KEYS = [
        'site_name', 'site_tagline', 'top_notice', 'phone', 'hotline', 'email', 'address', 'footer_about',
        'facebook', 'instagram', 'youtube', 'tiktok', 'whatsapp', 'messenger',
        'shipping_inside', 'shipping_outside', 'free_shipping_min',
        'primary_color', 'secondary_color', 'meta_description', 'meta_keywords', 'copyright',
        'delivery_time_inside', 'delivery_time_outside', 'return_days', 'return_policy_note',
        'checkout_notice', 'checkout_button', 'order_success_message', 'footer_tagline',
        'crm_vip_amount', 'crm_inactive_days',
        'feature1_icon', 'feature1_title', 'feature1_text', 'feature2_icon', 'feature2_title', 'feature2_text',
        'feature3_icon', 'feature3_title', 'feature3_text', 'feature4_icon', 'feature4_title', 'feature4_text',
        'categories_title', 'categories_subtitle', 'top_selling_title', 'just_for_you_title', 'just_for_you_count',
        'testimonials_title', 'testimonials_subtitle',
        'promo_eyebrow', 'promo_title', 'promo_text', 'promo_button', 'promo_link', 'promo_after_section',
    ];

    /** Checkbox settings: stored as "1"/"0" so an unticked box is saved too. */
    private const TOGGLE_KEYS = [
        'home_show_features', 'home_show_categories', 'home_show_top_selling', 'home_show_promo',
        'home_show_brands', 'home_show_just_for_you', 'home_show_testimonials',
    ];

    private const IMAGE_KEYS = ['logo', 'logo_white', 'favicon', 'payment_banner', 'promo_image'];

    public function index()
    {
        return view('admin.settings');
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:100',
            'email' => 'nullable|email',
            'shipping_inside' => 'required|numeric|min:0',
            'shipping_outside' => 'required|numeric|min:0',
            'free_shipping_min' => 'nullable|numeric|min:0',
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo' => 'nullable|image|max:2048',
            'logo_white' => 'nullable|image|max:2048',
            'favicon' => 'nullable|mimes:png,ico,jpg,jpeg,svg,webp|max:1024',
            'payment_banner' => 'nullable|image|max:2048',
            'promo_image' => 'nullable|image|max:4096',
            'return_days' => 'nullable|integer|min:0|max:90',
            'just_for_you_count' => 'nullable|integer|min:0|max:60',
            'promo_after_section' => 'nullable|integer|min:0|max:20',
            'crm_vip_amount' => 'nullable|numeric|min:0',
            'crm_inactive_days' => 'nullable|integer|min:1',
        ]);

        // Only keys present in this form are saved, so other tabs' values are never wiped.
        $values = array_intersect_key($request->only(self::TEXT_KEYS), $request->all());
        if ($request->boolean('_has_toggles')) {
            foreach (self::TOGGLE_KEYS as $key) {
                $values[$key] = $request->boolean($key) ? '1' : '0';
            }
        }
        foreach (self::IMAGE_KEYS as $key) {
            if ($request->hasFile($key)) {
                $values[$key] = upload_file($request->file($key), 'settings', setting($key));
            }
        }
        Setting::put($values);

        return back()->with('success', 'Settings saved.');
    }

    public function profile()
    {
        return view('admin.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', 'Profile updated.');
    }
}

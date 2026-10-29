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
    ];

    private const IMAGE_KEYS = ['logo', 'logo_white', 'favicon', 'payment_banner'];

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
        ]);

        $values = $request->only(self::TEXT_KEYS);
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

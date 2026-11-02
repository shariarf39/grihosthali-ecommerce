<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\Setting;
use Illuminate\Http\Request;

/** Homepage 3D showcase gallery: section texts + images. */
class GalleryController extends Controller
{
    public function index()
    {
        $images = GalleryImage::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.gallery.index', compact('images'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'gallery_eyebrow' => 'nullable|string|max:60',
            'gallery_title' => 'nullable|string|max:80',
            'gallery_subtitle' => 'nullable|string|max:200',
            'gallery_button_text' => 'nullable|string|max:50',
            'gallery_button_link' => 'nullable|string|max:255',
            'gallery_speed' => 'nullable|integer|min:1500|max:10000',
        ]);
        $data['home_show_gallery'] = $request->boolean('home_show_gallery') ? '1' : '0';
        Setting::put($data);

        return back()->with('success', 'Gallery section saved.');
    }

    /** Upload one or many images at once. */
    public function store(Request $request)
    {
        $request->validate([
            'images' => 'required|array|max:20',
            'images.*' => 'image|max:4096',
        ], ['images.required' => 'Choose at least one image.']);

        $order = (int) GalleryImage::max('sort_order');
        foreach ($request->file('images') as $file) {
            GalleryImage::create(['image' => upload_file($file, 'gallery'), 'sort_order' => ++$order]);
        }

        return back()->with('success', count($request->file('images')) . ' image(s) added.');
    }

    public function update(Request $request, GalleryImage $gallery)
    {
        $data = $request->validate([
            'caption' => 'nullable|string|max:120',
            'link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:4096',
        ]);
        if ($request->hasFile('image')) {
            $data['image'] = upload_file($request->file('image'), 'gallery', $this->ownedFile($gallery));
        } else {
            unset($data['image']);
        }
        $data['sort_order'] = $data['sort_order'] ?? $gallery->sort_order;
        $data['is_active'] = $request->boolean('is_active');
        $gallery->update($data);

        return back()->with('success', 'Image updated.');
    }

    public function destroy(GalleryImage $gallery)
    {
        delete_upload($this->ownedFile($gallery));
        $gallery->delete();

        return back()->with('success', 'Image removed.');
    }

    /** Only files uploaded through the gallery may be deleted — some entries reuse product images. */
    private function ownedFile(GalleryImage $gallery): ?string
    {
        return str_starts_with((string) $gallery->image, 'gallery/') ? $gallery->image : null;
    }
}

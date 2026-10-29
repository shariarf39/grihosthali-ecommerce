<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('sort_order')->get();

        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.form', ['slider' => new Slider(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['image'] = upload_file($request->file('image'), 'sliders');
        Slider::create($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider created.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.form', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $data = $this->validated($request, false);
        if ($request->hasFile('image')) {
            $data['image'] = upload_file($request->file('image'), 'sliders', $slider->image);
        }
        $slider->update($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider updated.');
    }

    public function destroy(Slider $slider)
    {
        delete_upload($slider->image);
        $slider->delete();

        return back()->with('success', 'Slider deleted.');
    }

    private function validated(Request $request, bool $imageRequired): array
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'image' => ($imageRequired ? 'required' : 'nullable') . '|image|max:4096',
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        unset($data['image']);

        return $data;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        $sliders = Slider::where('is_active', true)->orderBy('sort_order')->get();

        $featuredCategories = Category::active()->orderBy('sort_order')->get();

        $topSelling = Product::active()->where('is_top_selling', true)->latest()->take(10)->get();

        $brands = Brand::active()->get();

        $homeSections = Category::active()->where('show_on_home', true)->orderBy('sort_order')->get()
            ->map(function ($category) {
                $ids = $category->children()->pluck('id')->push($category->id);
                $category->setRelation('homeProducts', Product::active()->whereIn('category_id', $ids)->latest()->take(10)->get());

                return $category;
            })
            ->filter(fn ($c) => $c->homeProducts->isNotEmpty());

        $justForYou = Product::active()->inRandomOrder()->take(15)->get();

        $testimonials = Testimonial::where('is_active', true)->latest()->get();

        return view('home', compact('sliders', 'featuredCategories', 'topSelling', 'brands', 'homeSections', 'justForYou', 'testimonials'));
    }
}

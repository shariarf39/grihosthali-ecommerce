<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Services\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Cart::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::defaultView('partials.pagination');

        View::composer('layouts.app', function ($view) {
            $view->with('menuCategories', Category::active()->whereNull('parent_id')->where('show_in_menu', true)
                ->with(['children' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')->get());
            $view->with('cartCount', app(Cart::class)->count());
        });

        View::composer('admin.layouts.app', function ($view) {
            $view->with('pendingOrders', Order::where('status', 'pending')->count());
            $view->with('badges', [
                'returns' => \App\Models\OrderReturn::where('status', 'requested')->count(),
                'follow_ups' => \App\Models\CrmActivity::pendingFollowUps()->where('due_at', '<=', now()->endOfDay())->count(),
                'messages' => \App\Models\ContactMessage::where('is_read', false)->count(),
                'low_stock' => \App\Models\Product::lowStock()->count(),
                'reviews' => \App\Models\Review::where('is_approved', false)->count(),
            ]);
        });
    }
}

<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Services\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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

        // Admin permissions come from the user's role; Super Admin ("*") passes every check.
        Gate::before(fn ($user, string $ability) => $user->hasPermission($ability) ? true : null);
        Paginator::defaultView('partials.pagination');

        View::composer('layouts.app', function ($view) {
            $view->with('menuCategories', Category::active()->whereNull('parent_id')->where('show_in_menu', true)
                ->with(['children' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')->get());
            $view->with('cartCount', app(Cart::class)->count());
        });

        View::composer('admin.layouts.app', function ($view) {
            $user = auth()->user();
            $badges = [
                'orders' => $user->can('orders.view') ? Order::where('status', 'pending')->count() : 0,
                'returns' => $user->can('returns.manage') ? \App\Models\OrderReturn::where('status', 'requested')->count() : 0,
                'follow_ups' => $user->can('customers.manage') ? \App\Models\CrmActivity::pendingFollowUps()->where('due_at', '<=', now()->endOfDay())->count() : 0,
                'messages' => $user->can('messages.manage') ? \App\Models\ContactMessage::where('is_read', false)->count() : 0,
                'low_stock' => $user->can('inventory.manage') ? \App\Models\Product::lowStock()->count() : 0,
                'reviews' => $user->can('catalog.manage') ? \App\Models\Review::where('is_approved', false)->count() : 0,
                'mail_failed' => $user->can('integrations.manage') ? \App\Models\MailLog::where('status', 'failed')->where('created_at', '>=', now()->subDay())->count() : 0,
            ];

            // Bell dropdown: only things this admin is allowed to act on.
            $notifications = collect([
                ['count' => $badges['orders'], 'label' => 'pending orders', 'hint' => 'Waiting for confirmation', 'icon' => 'fa-solid fa-bag-shopping', 'tone' => 'green', 'url' => $user->can('orders.view') ? route('admin.orders.index', ['status' => 'pending']) : '#'],
                ['count' => $badges['returns'], 'label' => 'return requests', 'hint' => 'Need review', 'icon' => 'fa-solid fa-rotate-left', 'tone' => 'gold', 'url' => $user->can('returns.manage') ? route('admin.returns.index', ['status' => 'requested']) : '#'],
                ['count' => $badges['follow_ups'], 'label' => 'follow-ups due', 'hint' => 'Customer tasks for today', 'icon' => 'fa-regular fa-bell', 'tone' => 'blue', 'url' => $user->can('customers.manage') ? route('admin.crm.follow-ups') : '#'],
                ['count' => $badges['low_stock'], 'label' => 'low stock items', 'hint' => 'Restock soon', 'icon' => 'fa-solid fa-warehouse', 'tone' => 'red', 'url' => $user->can('inventory.manage') ? route('admin.inventory.index', ['filter' => 'low']) : '#'],
                ['count' => $badges['messages'], 'label' => 'unread messages', 'hint' => 'From the contact form', 'icon' => 'fa-solid fa-inbox', 'tone' => 'blue', 'url' => $user->can('messages.manage') ? route('admin.messages.index') : '#'],
                ['count' => $badges['reviews'], 'label' => 'reviews to approve', 'hint' => 'Customer reviews', 'icon' => 'fa-solid fa-star', 'tone' => 'gold', 'url' => $user->can('catalog.manage') ? route('admin.reviews.index', ['status' => 'pending']) : '#'],
                ['count' => $badges['mail_failed'], 'label' => 'emails failed', 'hint' => 'In the last 24 hours', 'icon' => 'fa-solid fa-envelope-circle-check', 'tone' => 'red', 'url' => $user->can('integrations.manage') ? route('admin.integrations.email.logs', ['status' => 'failed']) : '#'],
            ])->filter(fn ($n) => $n['count'] > 0)->values();

            $view->with(compact('badges', 'notifications'));
        });
    }
}

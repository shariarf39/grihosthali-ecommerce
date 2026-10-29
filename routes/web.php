<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{slug}', [ShopController::class, 'category'])->name('category.show');
Route::get('/brand/{slug}', [ShopController::class, 'brand'])->name('brand.show');
Route::get('/search/suggest', [ShopController::class, 'suggest'])->name('search.suggest');

Route::get('/products/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::post('/products/{product}/review', [ProductController::class, 'review'])->name('product.review');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/data', [CartController::class, 'data'])->name('cart.data');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/buy-now', [CartController::class, 'buyNow'])->name('cart.buy-now');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
Route::post('/checkout/coupon/remove', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
Route::get('/order-success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

Route::match(['get', 'post'], '/track-order', [PageController::class, 'trackOrder'])->name('track-order');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

Route::get('/returns/{orderNumber}', [ReturnController::class, 'create'])->name('returns.create');
Route::post('/returns/{orderNumber}', [ReturnController::class, 'store'])->name('returns.store');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactStore'])->name('contact.store');
Route::post('/subscribe', [PageController::class, 'subscribe'])->name('subscribe');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/orders/{orderNumber}', [AccountController::class, 'order'])->name('order');
    Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile');
    Route::post('/password', [AccountController::class, 'updatePassword'])->name('password');
});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin', 'admin.log'])->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('search', Admin\SearchController::class)->name('search');
        Route::get('profile', [Admin\SettingController::class, 'profile'])->name('profile');
        Route::post('profile', [Admin\SettingController::class, 'updateProfile'])->name('profile.update');

        Route::middleware('can:orders.view')->group(function () {
            Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
            Route::get('orders/{order}/invoice', [Admin\OrderController::class, 'invoice'])->name('orders.invoice');
        });
        Route::put('orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update')->middleware('can:orders.manage');
        Route::delete('orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy')->middleware('can:orders.delete');

        Route::middleware('can:courier.manage')->group(function () {
            Route::get('shipments', [Admin\CourierController::class, 'shipments'])->name('shipments.index');
            Route::post('shipments/sync-all', [Admin\CourierController::class, 'syncAll'])->name('shipments.sync-all');
            Route::post('shipments/bulk-send', [Admin\CourierController::class, 'bulkSend'])->name('shipments.bulk-send');
            Route::post('orders/{order}/courier', [Admin\CourierController::class, 'send'])->name('shipments.send');
            Route::post('shipments/{shipment}/sync', [Admin\CourierController::class, 'sync'])->name('shipments.sync');
            Route::post('shipments/{shipment}/cancel', [Admin\CourierController::class, 'cancel'])->name('shipments.cancel');
            Route::post('shipments/{shipment}/detach', [Admin\CourierController::class, 'detach'])->name('shipments.detach');
            Route::get('couriers/{slug}/locations', [Admin\CourierController::class, 'locations'])->name('couriers.locations');
        });

        Route::middleware('can:returns.manage')->group(function () {
            Route::get('returns', [Admin\ReturnController::class, 'index'])->name('returns.index');
            Route::get('orders/{order}/return', [Admin\ReturnController::class, 'create'])->name('returns.create');
            Route::post('orders/{order}/return', [Admin\ReturnController::class, 'store'])->name('returns.store');
            Route::get('returns/{return}', [Admin\ReturnController::class, 'show'])->name('returns.show');
            Route::put('returns/{return}', [Admin\ReturnController::class, 'update'])->name('returns.update');
        });

        Route::get('reports', [Admin\DashboardController::class, 'reports'])->name('reports')->middleware('can:reports.view');

        Route::middleware('can:customers.manage')->group(function () {
            Route::get('crm', [Admin\CrmController::class, 'index'])->name('crm.index');
            Route::get('crm/export', [Admin\CrmController::class, 'export'])->name('crm.export');
            Route::get('crm/follow-ups', [Admin\CrmController::class, 'followUps'])->name('crm.follow-ups');
            Route::get('crm/{customer}', [Admin\CrmController::class, 'show'])->name('crm.show');
            Route::put('crm/{customer}', [Admin\CrmController::class, 'update'])->name('crm.update');
            Route::post('crm/{customer}/activities', [Admin\CrmController::class, 'storeActivity'])->name('crm.activities.store');
            Route::post('crm/activities/{activity}/complete', [Admin\CrmController::class, 'completeActivity'])->name('crm.activities.complete');
            Route::delete('crm/activities/{activity}', [Admin\CrmController::class, 'destroyActivity'])->name('crm.activities.destroy');
            Route::get('customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{user}', [Admin\CustomerController::class, 'show'])->name('customers.show');
            Route::delete('customers/{user}', [Admin\CustomerController::class, 'destroy'])->name('customers.destroy');
        });

        Route::middleware('can:messages.manage')->group(function () {
            Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
            Route::get('messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
            Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');
            Route::get('subscribers', [Admin\MessageController::class, 'subscribers'])->name('subscribers.index');
            Route::delete('subscribers/{subscriber}', [Admin\MessageController::class, 'destroySubscriber'])->name('subscribers.destroy');
        });

        Route::middleware('can:products.manage')->group(function () {
            Route::resource('products', Admin\ProductController::class)->except('show');
            Route::post('products/{product}/toggle', [Admin\ProductController::class, 'toggle'])->name('products.toggle');
        });

        Route::middleware('can:catalog.manage')->group(function () {
            Route::resource('categories', Admin\CategoryController::class)->except('show');
            Route::resource('brands', Admin\BrandController::class)->except('show');
            Route::get('reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
            Route::post('reviews/{review}/toggle', [Admin\ReviewController::class, 'toggle'])->name('reviews.toggle');
            Route::delete('reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
        });

        Route::middleware('can:inventory.manage')->group(function () {
            Route::get('inventory', [Admin\InventoryController::class, 'index'])->name('inventory.index');
            Route::post('inventory/{product}/adjust', [Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');
            Route::get('inventory/movements', [Admin\InventoryController::class, 'movements'])->name('inventory.movements');
            Route::get('inventory/export', [Admin\InventoryController::class, 'export'])->name('inventory.export');
        });

        Route::middleware('can:marketing.manage')->group(function () {
            Route::resource('coupons', Admin\CouponController::class)->except('show');
            Route::resource('sliders', Admin\SliderController::class)->except('show');
            Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
        });

        Route::resource('pages', Admin\PageController::class)->except('show')->middleware('can:pages.manage');

        Route::middleware('can:settings.manage')->group(function () {
            Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings');
            Route::post('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        });

        Route::middleware('can:integrations.manage')->prefix('integrations')->name('integrations.')->group(function () {
            Route::get('couriers', [Admin\IntegrationController::class, 'couriers'])->name('couriers');
            Route::post('couriers/general', [Admin\IntegrationController::class, 'updateCourierGeneral'])->name('couriers.general');
            Route::post('couriers/{slug}', [Admin\IntegrationController::class, 'updateCourier'])->name('couriers.update');
            Route::post('couriers/{slug}/test', [Admin\IntegrationController::class, 'testCourier'])->name('couriers.test');
            Route::get('email', [Admin\IntegrationController::class, 'email'])->name('email');
            Route::post('email', [Admin\IntegrationController::class, 'updateEmail'])->name('email.update');
            Route::post('email/test', [Admin\IntegrationController::class, 'testEmail'])->name('email.test');
            Route::get('email/logs', [Admin\IntegrationController::class, 'mailLogs'])->name('email.logs');
        });

        Route::middleware('can:staff.manage')->group(function () {
            Route::resource('staff', Admin\StaffController::class)->except('show')->parameters(['staff' => 'staff']);
            Route::resource('roles', Admin\RoleController::class)->except('show');
            Route::get('activity-log', [Admin\StaffController::class, 'activity'])->name('activity');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Courier webhooks (status push from Steadfast / Pathao / CarryBee)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/courier/{slug}/{token}', \App\Http\Controllers\CourierWebhookController::class)->name('webhooks.courier');

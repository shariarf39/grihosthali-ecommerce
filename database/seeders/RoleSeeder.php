<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;

/** Built-in admin roles. Safe to re-run: php artisan db:seed --class=RoleSeeder */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $all = Permissions::all();
        $roles = [
            'super-admin' => ['Super Admin', 'Full access, including admin users and integrations', ['*']],
            'manager' => ['Store Manager', 'Runs the whole store except staff and API setup',
                array_values(array_diff($all, ['staff.manage', 'integrations.manage']))],
            'order-manager' => ['Order Manager', 'Confirms orders, books couriers, handles returns',
                ['dashboard.view', 'orders.view', 'orders.manage', 'courier.manage', 'returns.manage', 'customers.manage', 'messages.manage']],
            'inventory-manager' => ['Inventory Manager', 'Products, stock in/out and catalog',
                ['dashboard.view', 'orders.view', 'products.manage', 'catalog.manage', 'inventory.manage']],
            'support' => ['Customer Support', 'Answers customers, follows up, handles returns',
                ['orders.view', 'returns.manage', 'customers.manage', 'messages.manage']],
            'content-editor' => ['Content Editor', 'Products, banners, coupons and pages',
                ['products.manage', 'catalog.manage', 'marketing.manage', 'pages.manage']],
        ];

        foreach ($roles as $slug => [$name, $description, $permissions]) {
            Role::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'permissions' => $permissions,
                'is_system' => true,
            ]);
        }

        // Existing admins without a role become Super Admins so nobody is locked out.
        $super = Role::where('slug', 'super-admin')->first();
        User::where('is_admin', true)->whereNull('role_id')->update(['role_id' => $super->id]);
    }
}

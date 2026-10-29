<?php

namespace App\Support;

/** Every permission an admin role can be granted, grouped for the role editor. */
class Permissions
{
    public const GROUPS = [
        'Sales' => [
            'dashboard.view' => 'View dashboard & stats',
            'orders.view' => 'View orders',
            'orders.manage' => 'Update orders (status, payment, notes)',
            'orders.delete' => 'Delete orders',
            'courier.manage' => 'Send orders to courier & sync tracking',
            'returns.manage' => 'Manage returns & refunds',
            'reports.view' => 'View sales reports',
        ],
        'Customers' => [
            'customers.manage' => 'CRM: customers, notes, follow-ups, block',
            'messages.manage' => 'Contact messages & subscribers',
        ],
        'Catalog' => [
            'products.manage' => 'Products',
            'catalog.manage' => 'Categories, brands & reviews',
            'inventory.manage' => 'Inventory: stock in/out, history',
        ],
        'Website' => [
            'marketing.manage' => 'Coupons, sliders & testimonials',
            'pages.manage' => 'Static pages',
            'settings.manage' => 'Store settings & homepage',
        ],
        'System' => [
            'integrations.manage' => 'Courier APIs & email (SMTP) setup',
            'staff.manage' => 'Admin users, roles & activity log',
        ],
    ];

    public static function all(): array
    {
        return array_merge(...array_values(array_map('array_keys', self::GROUPS)));
    }
}

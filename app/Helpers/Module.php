<?php

namespace App\Helpers;

use App\Models\Setting;

class Module
{
    private static array $cache = [];

    public static function isActive(string $key): bool
    {
        if (!array_key_exists($key, self::$cache)) {
            self::$cache[$key] = Setting::get('module_' . $key, '1') === '1';
        }
        return self::$cache[$key];
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }

    public static function definitions(): array
    {
        return [
            // ── Inventory ────────────────────────────────────────
            'products'              => ['group' => 'Inventory', 'label' => 'Products',         'icon' => 'bi-box-seam-fill',        'desc' => 'Product catalog, pricing and barcode assignment'],
            'brands'                => ['group' => 'Inventory', 'label' => 'Brands',           'icon' => 'bi-stars',                'desc' => 'Brand management for products'],
            'categories'            => ['group' => 'Inventory', 'label' => 'Categories',       'icon' => 'bi-tags-fill',            'desc' => 'Product category management'],
            'barcodes'              => ['group' => 'Inventory', 'label' => 'Barcodes',         'icon' => 'bi-upc-scan',             'desc' => 'Barcode generation, printing and assignment'],
            'add_stock'             => ['group' => 'Inventory', 'label' => 'Add Stock',        'icon' => 'bi-plus-square-fill',     'desc' => 'Add incoming stock to existing products'],
            'stock_movements'       => ['group' => 'Inventory', 'label' => 'Stock Movements',  'icon' => 'bi-arrow-left-right',     'desc' => 'View stock in/out movement log'],

            // ── Sales ────────────────────────────────────────────
            'sales_history'         => ['group' => 'Sales', 'label' => 'Sales History',        'icon' => 'bi-receipt',              'desc' => 'View and search past sales invoices'],
            'sale_returns'          => ['group' => 'Sales', 'label' => 'Sale Returns',         'icon' => 'bi-arrow-return-left',    'desc' => 'Customer returns and refund management'],
            'customers'             => ['group' => 'Sales', 'label' => 'Customers',            'icon' => 'bi-people-fill',          'desc' => 'Customer management and loyalty points'],

            // ── Finance ──────────────────────────────────────────
            'purchases'             => ['group' => 'Finance', 'label' => 'Purchases',          'icon' => 'bi-truck',                'desc' => 'Suppliers, Purchase Orders, GRN (Goods Received)'],
            'expenses'              => ['group' => 'Finance', 'label' => 'Expenses',           'icon' => 'bi-cash-coin',            'desc' => 'Expense categories and expense tracking'],

            // ── Reports ──────────────────────────────────────────
            'report_daily'          => ['group' => 'Reports', 'label' => 'Daily Report',       'icon' => 'bi-calendar-check-fill',  'desc' => 'Daily sales report with payment breakdown'],
            'report_monthly'        => ['group' => 'Reports', 'label' => 'Monthly Report',     'icon' => 'bi-calendar-month',       'desc' => 'Month-wise sales and daily order summary'],
            'report_profit_loss'    => ['group' => 'Reports', 'label' => 'Profit & Loss',      'icon' => 'bi-graph-up-arrow',       'desc' => 'Revenue, COGS, expenses and net profit'],
            'report_gst'            => ['group' => 'Reports', 'label' => 'GST Report',         'icon' => 'bi-percent',              'desc' => 'Output/Input GST and net payable summary'],
            'report_inventory'      => ['group' => 'Reports', 'label' => 'Inventory Report',   'icon' => 'bi-clipboard-data-fill',  'desc' => 'Product-wise stock quantity and valuation'],
            'report_cashier'        => ['group' => 'Reports', 'label' => 'Cashier Report',     'icon' => 'bi-person-badge',         'desc' => 'Cashier-wise sales performance report'],
            'report_stock_movement' => ['group' => 'Reports', 'label' => 'Stock Movement',     'icon' => 'bi-bar-chart-steps',      'desc' => 'Stock in/out movement history report'],
        ];
    }
}
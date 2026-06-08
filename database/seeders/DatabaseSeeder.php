<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Spatie Roles & Permissions ─────────────────────────────────────────
        $permissions = [
            // Dashboard
            'view dashboard',
            // Products
            'view products', 'create products', 'edit products', 'delete products',
            // Categories
            'view categories', 'manage categories',
            // Brands
            'view brands', 'manage brands',
            // Inventory / Stock
            'view stock', 'add stock', 'adjust stock',
            // Barcode
            'print barcodes',
            // POS / Sales
            'pos sales', 'view sales',
            // Sale Returns
            'process sale returns',
            // Customers
            'view customers', 'manage customers',
            // Suppliers
            'view suppliers', 'manage suppliers',
            // Purchases
            'view purchases', 'create purchases', 'manage purchases',
            // GRN
            'view grn', 'create grn',
            // Expenses
            'view expenses', 'manage expenses',
            // Reports
            'view reports',
            // Users
            'view users', 'manage users',
            // Settings
            'manage settings',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        $manager    = Role::firstOrCreate(['name' => 'manager',     'guard_name' => 'web']);
        $cashier    = Role::firstOrCreate(['name' => 'cashier',     'guard_name' => 'web']);

        // Super Admin: all permissions
        $superAdmin->syncPermissions(Permission::all());

        // Admin: all except manage settings
        $admin->syncPermissions(Permission::where('name', '!=', 'manage settings')->get());

        // Manager: no users, no settings
        $manager->syncPermissions(Permission::whereNotIn('name', ['view users','manage users','manage settings'])->get());

        // Cashier: limited
        $cashier->syncPermissions([
            'view dashboard', 'pos sales', 'view sales', 'view stock', 'add stock',
        ]);

        // ── Users ──────────────────────────────────────────────────────────────
        $sa = User::firstOrCreate(['email' => 'superadmin@mayankfootware.com'], [
            'name' => 'Super Admin', 'password' => Hash::make('Super@123'),
            'role' => 'super_admin', 'is_active' => true,
        ]);
        $sa->syncRoles('super_admin');

        $adm = User::firstOrCreate(['email' => 'admin@mayankfootware.com'], [
            'name' => 'Admin', 'password' => Hash::make('admin123'),
            'role' => 'admin', 'is_active' => true,
        ]);
        $adm->syncRoles('admin');

        $mgr = User::firstOrCreate(['email' => 'manager@mayankfootware.com'], [
            'name' => 'Store Manager', 'password' => Hash::make('manager123'),
            'role' => 'manager', 'is_active' => true,
        ]);
        $mgr->syncRoles('manager');

        $cas = User::firstOrCreate(['email' => 'cashier@mayankfootware.com'], [
            'name' => 'Cashier', 'password' => Hash::make('cashier123'),
            'role' => 'cashier', 'is_active' => true,
        ]);
        $cas->syncRoles('cashier');

        // ── Categories ─────────────────────────────────────────────────────────
        $categories = [
            ['name' => "Men's Shoes",       'slug' => 'mens-shoes'],
            ['name' => "Women's Shoes",      'slug' => 'womens-shoes'],
            ['name' => "Kids Shoes",         'slug' => 'kids-shoes'],
            ['name' => "Sports Shoes",       'slug' => 'sports-shoes'],
            ['name' => "Sandals & Slippers", 'slug' => 'sandals-slippers'],
            ['name' => "Formal Shoes",       'slug' => 'formal-shoes'],
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], array_merge($cat, ['is_active' => true]));
        }

        // ── Brands ─────────────────────────────────────────────────────────────
        $brands = ['Nike', 'Adidas', 'Puma', 'Bata', 'Lotto', 'Paragon', 'Liberty', 'Woodland'];
        foreach ($brands as $b) {
            Brand::firstOrCreate(['slug' => Str::slug($b)], ['name' => $b, 'is_active' => true]);
        }

        // ── Expense Categories ─────────────────────────────────────────────────
        $expCats = [
            ['name' => 'Rent',          'icon' => 'bi-building'],
            ['name' => 'Electricity',   'icon' => 'bi-lightning-charge'],
            ['name' => 'Salary',        'icon' => 'bi-person-badge'],
            ['name' => 'Transport',     'icon' => 'bi-truck'],
            ['name' => 'Packaging',     'icon' => 'bi-box-seam'],
            ['name' => 'Maintenance',   'icon' => 'bi-tools'],
            ['name' => 'Marketing',     'icon' => 'bi-megaphone'],
            ['name' => 'Miscellaneous', 'icon' => 'bi-three-dots'],
        ];
        foreach ($expCats as $ec) {
            ExpenseCategory::firstOrCreate(['name' => $ec['name']], ['icon' => $ec['icon']]);
        }

        // ── Settings ───────────────────────────────────────────────────────────
        $defaults = [
            'business_name'           => 'Mayank Footware',
            'business_address'        => 'Shop No. 12, Main Market, City',
            'business_phone'          => '98765-43210',
            'business_gst'            => '24XXXXX1234X1Z5',
            'currency_symbol'         => '₹',
            'tax_percent'             => '0',
            'loyalty_points_per_rupee'=> '1',
            'points_to_rupee'         => '100',
            'receipt_footer'          => 'Thank You! Come Again!',
            'receipt_exchange_policy' => 'Exchange within 7 days (with bill only)',
            'low_stock_alert_qty'     => '5',
            'barcode_label_width'     => '60',
            'barcode_label_height'    => '30',
        ];
        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => 'general']);
        }

        // ── Products ───────────────────────────────────────────────────────────
        $products = [
            ['name' => 'Nike Air Max 270', 'sku' => 'NK-AM270-8B',  'cat' => "Men's Shoes",       'brand' => 'Nike',    'size' => '8',  'color' => 'Black', 'purchase_price' => 2500, 'selling_price' => 4999, 'mrp' => 5999,  'stock' => 15],
            ['name' => 'Nike Air Max 270', 'sku' => 'NK-AM270-9B',  'cat' => "Men's Shoes",       'brand' => 'Nike',    'size' => '9',  'color' => 'Black', 'purchase_price' => 2500, 'selling_price' => 4999, 'mrp' => 5999,  'stock' => 10],
            ['name' => 'Adidas Ultraboost','sku' => 'AD-UB-8W',     'cat' => "Men's Shoes",       'brand' => 'Adidas',  'size' => '8',  'color' => 'White', 'purchase_price' => 3000, 'selling_price' => 5999, 'mrp' => 7499,  'stock' => 8],
            ['name' => 'Bata Ladies Court','sku' => 'BT-LC-5BK',    'cat' => "Women's Shoes",     'brand' => 'Bata',    'size' => '5',  'color' => 'Black', 'purchase_price' => 800,  'selling_price' => 1499, 'mrp' => 1999,  'stock' => 12],
            ['name' => 'Bata Ladies Court','sku' => 'BT-LC-6BK',    'cat' => "Women's Shoes",     'brand' => 'Bata',    'size' => '6',  'color' => 'Black', 'purchase_price' => 800,  'selling_price' => 1499, 'mrp' => 1999,  'stock' => 10],
            ['name' => 'Nike Kids Shoes',  'sku' => 'NK-KD-3RD',    'cat' => "Kids Shoes",        'brand' => 'Nike',    'size' => '3',  'color' => 'Red',   'purchase_price' => 1200, 'selling_price' => 2299, 'mrp' => 2999,  'stock' => 6],
            ['name' => 'Puma Sports',      'sku' => 'PM-SR-9BL',    'cat' => "Sports Shoes",      'brand' => 'Puma',    'size' => '9',  'color' => 'Blue',  'purchase_price' => 1800, 'selling_price' => 3499, 'mrp' => 4499,  'stock' => 4],
            ['name' => 'Lotto Formal',     'sku' => 'LT-FO-9BR',    'cat' => "Formal Shoes",      'brand' => 'Lotto',   'size' => '9',  'color' => 'Brown', 'purchase_price' => 1500, 'selling_price' => 2999, 'mrp' => 3999,  'stock' => 7],
            ['name' => 'Paragon Slippers', 'sku' => 'PG-SL-9BK',   'cat' => "Sandals & Slippers",'brand' => 'Paragon', 'size' => '9',  'color' => 'Black', 'purchase_price' => 200,  'selling_price' => 399,  'mrp' => 499,   'stock' => 20],
        ];
        foreach ($products as $p) {
            $cat   = Category::where('name', $p['cat'])->first();
            $brand = Brand::where('name', $p['brand'])->first();
            Product::firstOrCreate(['sku' => $p['sku']], [
                'category_id'    => $cat?->id,
                'brand_id'       => $brand?->id,
                'name'           => $p['name'],
                'brand'          => $p['brand'],
                'size'           => $p['size'],
                'color'          => $p['color'],
                'purchase_price' => $p['purchase_price'],
                'selling_price'  => $p['selling_price'],
                'mrp'            => $p['mrp'],
                'stock_quantity' => $p['stock'],
                'alert_quantity' => 5,
                'is_active'      => true,
                'barcode'        => '8' . str_pad(random_int(0, 9999999999), 12, '0', STR_PAD_LEFT),
            ]);
        }
    }
}

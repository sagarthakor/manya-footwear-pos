<?php

namespace App\Http\Controllers;

use App\Helpers\Module;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $today = today();
        $month = now()->startOfMonth();

        if ($user->isManager()) {
            return $this->managerView($today, $month, $user);
        }
        return $this->cashierView($today, $user);
    }

    /* ── Manager / Admin / Custom Role (merged, permission-aware) ── */
    private function managerView($today, $month, $user)
    {
        $canViewSales = $user->can('view sales');

        // ── Core sales (gated by view sales permission) ───────────
        $todayRevenue = $todayOrders = $todayCashIn = $todayUpiIn = $todayCardIn = 0;
        $monthSales = $monthOrders = 0;
        if ($canViewSales) {
            $todayRevenue = Sale::whereDate('created_at', $today)->where('status', 'completed')->sum('total_amount');
            $todayOrders  = Sale::whereDate('created_at', $today)->where('status', 'completed')->count();
            $todayCashIn  = Sale::whereDate('created_at', $today)->where('status', 'completed')->where('payment_method', 'cash')->sum('total_amount');
            $todayUpiIn   = Sale::whereDate('created_at', $today)->where('status', 'completed')->where('payment_method', 'upi')->sum('total_amount');
            $todayCardIn  = Sale::whereDate('created_at', $today)->where('status', 'completed')->whereNotIn('payment_method', ['cash', 'upi'])->sum('total_amount');
            $monthSales   = Sale::where('created_at', '>=', $month)->where('status', 'completed')->sum('total_amount');
            $monthOrders  = Sale::where('created_at', '>=', $month)->where('status', 'completed')->count();
        }
        $totalStockItems = Product::where('is_active', true)->sum('stock_quantity');

        // ── Purchases (module + permission) ──────────────────────
        $todayPurchase = $monthPurchase = 0;
        $recentGrns    = collect();
        $pendingPOs    = 0;
        if (Module::isActive('purchases') && $user->can('view purchases')) {
            $todayPurchase = GrnItem::whereHas('grn', fn($q) => $q->whereDate('created_at', $today))->sum('total_price');
            $monthPurchase = GrnItem::whereHas('grn', fn($q) => $q->where('created_at', '>=', $month))->sum('total_price');
            $recentGrns    = Grn::with('supplier')->latest()->limit(5)->get();
            $pendingPOs    = PurchaseOrder::whereIn('status', ['draft', 'sent', 'confirmed'])->count();
        }

        // ── Expenses (module + permission) ───────────────────────
        $todayExpenses = $monthExpenses = $todayProfit = $monthProfit = 0;
        if (Module::isActive('expenses') && $user->can('view expenses')) {
            $todayExpenses = Expense::where('expense_date', $today)->sum('amount');
            $monthExpenses = Expense::where('expense_date', '>=', $month)->sum('amount');
            $todayProfit   = $todayRevenue - $todayPurchase - $todayExpenses;
            $monthProfit   = $monthSales - $monthPurchase - $monthExpenses;
        }

        // ── Returns (module + permission) ────────────────────────
        $todayReturns  = 0;
        $recentReturns = collect();
        if (Module::isActive('sale_returns') && $user->can('process sale returns')) {
            $todayReturns  = SaleReturn::whereDate('created_at', $today)->count();
            $recentReturns = SaleReturn::with(['sale', 'user'])->latest()->limit(5)->get();
        }

        // ── Inventory (always for managers) ──────────────────────
        $totalProducts   = Product::where('is_active', true)->count();
        $totalBrands     = Brand::where('is_active', true)->count();
        $totalCategories = Category::where('is_active', true)->count();
        $outOfStockCount = Product::where('is_active', true)->where('stock_quantity', 0)->count();
        $lowStockItems   = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'alert_quantity')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')->limit(8)->get();
        $totalStockValue = $user->can('view reports')
            ? Product::where('is_active', true)->sum(DB::raw('stock_quantity * purchase_price'))
            : 0;

        // ── Customers (module + permission) ──────────────────────
        $totalCustomers    = $todayNewCustomers = 0;
        $topCustomers      = collect();
        if (Module::isActive('customers') && $user->can('view customers')) {
            $totalCustomers    = Customer::count();
            $todayNewCustomers = Customer::whereDate('created_at', $today)->count();
            if ($user->can('view reports')) {
                $topCustomers = DB::table('sales')
                    ->join('customers', 'sales.customer_id', '=', 'customers.id')
                    ->where('sales.status', 'completed')
                    ->select('customers.name', DB::raw('COUNT(*) as orders'), DB::raw('SUM(sales.total_amount) as total'))
                    ->groupBy('customers.id', 'customers.name')
                    ->orderByDesc('total')->limit(5)->get();
            }
        }

        // ── Reports / Analytics (permission) ─────────────────────
        $topProducts = $topBrands = $topCategories = $topCashiers = collect();
        $categoryPie = $monthlyChart = null;
        if ($user->can('view reports')) {
            $topProducts = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.status', 'completed')
                ->where('sales.created_at', '>=', now()->subDays(30))
                ->select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total_price) as revenue'))
                ->groupBy('product_name')->orderByDesc('qty')->limit(5)->get();

            $topBrands = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('brands', 'products.brand_id', '=', 'brands.id')
                ->where('sales.status', 'completed')
                ->where('sales.created_at', '>=', now()->subDays(30))
                ->select('brands.name', DB::raw('SUM(sale_items.quantity) as qty'), DB::raw('SUM(sale_items.total_price) as revenue'))
                ->groupBy('brands.id', 'brands.name')->orderByDesc('revenue')->limit(5)->get();

            $topCategories = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where('sales.status', 'completed')
                ->where('sales.created_at', '>=', now()->subDays(30))
                ->select('categories.name', DB::raw('SUM(sale_items.quantity) as qty'), DB::raw('SUM(sale_items.total_price) as revenue'))
                ->groupBy('categories.id', 'categories.name')->orderByDesc('revenue')->limit(5)->get();

            $topCashiers = DB::table('sales')
                ->join('users', 'sales.user_id', '=', 'users.id')
                ->where('sales.status', 'completed')
                ->where('sales.created_at', '>=', $month)
                ->select('users.name', DB::raw('COUNT(*) as orders'), DB::raw('SUM(sales.total_amount) as revenue'))
                ->groupBy('users.id', 'users.name')->orderByDesc('revenue')->limit(5)->get();

            $categoryPie = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where('sales.status', 'completed')->where('sales.created_at', '>=', $month)
                ->select('categories.name', DB::raw('SUM(sale_items.total_price) as revenue'))
                ->groupBy('categories.id', 'categories.name')->orderByDesc('revenue')->get();

            if (Module::isActive('purchases') && $user->can('view purchases')) {
                $monthlyChart = $this->monthlyComparison(6);
            }
        }

        $salesChart  = $canViewSales ? $this->dailySales(7) : ['labels' => [], 'values' => []];
        $recentSales = $canViewSales ? Sale::with(['customer', 'user', 'items'])->latest()->limit(8)->get() : collect();

        return view('dashboard', compact(
            'todayRevenue', 'todayOrders', 'todayCashIn', 'todayUpiIn', 'todayCardIn', 'todayPurchase',
            'todayExpenses', 'todayProfit', 'todayReturns',
            'monthSales', 'monthPurchase', 'monthExpenses', 'monthProfit', 'monthOrders',
            'totalProducts', 'totalStockValue', 'totalStockItems', 'totalBrands', 'totalCategories',
            'outOfStockCount', 'lowStockItems',
            'totalCustomers', 'todayNewCustomers', 'topCustomers',
            'topProducts', 'topBrands', 'topCategories', 'topCashiers',
            'salesChart', 'monthlyChart', 'categoryPie',
            'recentSales', 'recentGrns', 'recentReturns', 'pendingPOs'
        ));
    }

    /* ── Cashier ──────────────────────────────────────────── */
    private function cashierView($today, $user)
    {
        $canViewSales = $user->can('view sales');

        $myRevenue = $myOrders = $myItems = $myReturns = 0;
        $recentSales = collect();

        if ($canViewSales) {
            $myRevenue = Sale::whereDate('created_at', $today)->where('user_id', $user->id)->where('status', 'completed')->sum('total_amount');
            $myOrders  = Sale::whereDate('created_at', $today)->where('user_id', $user->id)->where('status', 'completed')->count();
            $myItems   = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereDate('sales.created_at', $today)
                ->where('sales.user_id', $user->id)
                ->where('sales.status', 'completed')
                ->sum('sale_items.quantity');
            $myReturns   = SaleReturn::whereDate('created_at', $today)->where('user_id', $user->id)->count();
            $recentSales = Sale::with(['customer', 'items'])->where('user_id', $user->id)->latest()->limit(8)->get();
        }

        $lowStockItems = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'alert_quantity')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')->limit(5)->get();
        $outOfStockCount = Product::where('is_active', true)->where('stock_quantity', 0)->count();

        return view('dashboard', compact(
            'myRevenue', 'myOrders', 'myItems', 'myReturns',
            'recentSales', 'lowStockItems', 'outOfStockCount'
        ));
    }

    public function salesChartApi(\Illuminate\Http\Request $request)
    {
        if (!auth()->user()->can('view sales')) {
            return response()->json(['labels' => [], 'values' => []]);
        }
        $days = (int) ($request->days ?? 7);
        return response()->json($this->dailySales(min($days, 30)));
    }

    private function dailySales(int $days): array
    {
        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $labels[] = $d->format($days <= 7 ? 'D d' : 'd M');
            $values[] = (float) Sale::whereDate('created_at', $d->toDateString())
                ->where('status', 'completed')->sum('total_amount');
        }
        return compact('labels', 'values');
    }

    private function monthlyComparison(int $months): array
    {
        $labels = [];
        $sales  = [];
        $purchases = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonths($i);
            $end   = (clone $start)->endOfMonth();
            $labels[]    = $start->format('M y');
            $sales[]     = (float) Sale::whereBetween('created_at', [$start, $end])->where('status', 'completed')->sum('total_amount');
            $purchases[] = (float) GrnItem::whereHas('grn', fn($q) => $q->whereBetween('created_at', [$start, $end]))->sum('total_price');
        }
        return compact('labels', 'sales', 'purchases');
    }
}

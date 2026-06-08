<?php

namespace App\Http\Controllers;

use App\Helpers\Module;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    private static array $knownPayments = ['cash', 'upi', 'card'];

    private function paymentFilter($query, ?string $payment)
    {
        if (!$payment || $payment === 'all') return $query;
        if ($payment === 'others') return $query->whereNotIn('payment_method', self::$knownPayments);
        return $query->where('payment_method', $payment);
    }

    /* ── Daily ───────────────────────────────────────────────── */
    public function daily(Request $request)
    {
        abort_unless(Module::isActive('report_daily'), 403);
        $date    = $request->date ? Carbon::parse($request->date) : today();
        $payment = $request->payment_method;

        $salesQuery = Sale::with(['customer', 'items', 'user'])
            ->whereDate('created_at', $date)->where('status', 'completed');
        $sales = $this->paymentFilter($salesQuery, $payment)->get();

        $totalRevenue = $sales->sum('total_amount');
        $totalOrders  = $sales->count();
        $totalItems   = $sales->sum(fn($s) => $s->items->sum('quantity'));
        $totalGST     = $sales->sum('tax_amount');

        $paymentBreakdown = $sales->groupBy('payment_method')
            ->map(fn($g) => ['count' => $g->count(), 'amount' => $g->sum('total_amount')]);

        $expenses     = Expense::with('expenseCategory')->whereDate('expense_date', $date)->get();
        $totalExpense = $expenses->sum('amount');
        $netProfit    = $totalRevenue - $totalExpense;

        return view('reports.daily', compact(
            'sales', 'date', 'payment', 'totalRevenue', 'totalOrders', 'totalItems',
            'totalGST', 'paymentBreakdown', 'expenses', 'totalExpense', 'netProfit'
        ));
    }

    /* ── Monthly ─────────────────────────────────────────────── */
    public function monthly(Request $request)
    {
        abort_unless(Module::isActive('report_monthly'), 403);
        $month   = $request->month ? Carbon::parse($request->month . '-01') : now()->startOfMonth();
        $start   = $month->copy()->startOfMonth();
        $end     = $month->copy()->endOfMonth();
        $payment = $request->payment_method;

        $baseQuery = fn() => $this->paymentFilter(
            Sale::where('status', 'completed')->whereBetween('created_at', [$start, $end]),
            $payment
        );

        $sales     = $baseQuery()->get();
        $dailyRows = $baseQuery()
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders, SUM(tax_amount) as gst')
            ->groupBy('date')->orderBy('date')->get();

        // Expenses not filtered by payment type
        $expenses = $payment ? 0 : Expense::whereBetween('expense_date', [$start, $end])->sum('amount');

        $monthlyData = [
            'revenue'  => $sales->sum('total_amount'),
            'orders'   => $sales->count(),
            'expenses' => $expenses,
            'gst'      => $sales->sum('tax_amount'),
            'daily'    => $dailyRows,
        ];

        return view('reports.monthly', compact('month', 'payment', 'monthlyData'));
    }

    /* ── Inventory ───────────────────────────────────────────── */
    public function inventory(Request $request)
    {
        abort_unless(Module::isActive('report_inventory'), 403);

        $query = Product::with('category')->where('is_active', true);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%")
                  ->orWhere('item_code', 'like', "%$s%")
                  ->orWhere('barcode', 'like', "%$s%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('brand_name')) {
            $query->where('brand', 'like', '%' . $request->brand_name . '%');
        }
        if ($request->stock === 'low') {
            $query->whereColumn('stock_quantity', '<=', 'alert_quantity')->where('stock_quantity', '>', 0);
        } elseif ($request->stock === 'out') {
            $query->where('stock_quantity', 0);
        } elseif ($request->stock === 'in') {
            $query->where('stock_quantity', '>', 0);
        }

        $products = $query->orderBy('category_id')->orderBy('name')->get();

        $totalValue       = $products->sum(fn($p) => $p->stock_quantity * $p->purchase_price);
        $totalRetailValue = $products->sum(fn($p) => $p->stock_quantity * $p->selling_price);
        $lowStockProducts = $products->filter(fn($p) => $p->isLowStock());

        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        $brands     = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();

        return view('reports.inventory', compact(
            'products', 'totalValue', 'totalRetailValue', 'lowStockProducts',
            'categories', 'brands'
        ));
    }

    /* ── Profit & Loss ───────────────────────────────────────── */
    public function profitLoss(Request $request)
    {
        abort_unless(Module::isActive('report_profit_loss'), 403);
        $start   = $request->from_date ? Carbon::parse($request->from_date) : now()->startOfMonth();
        $end     = $request->to_date   ? Carbon::parse($request->to_date)->endOfDay() : now()->endOfDay();
        $payment = $request->payment_method;

        $saleBase = $this->paymentFilter(
            Sale::where('status', 'completed')->whereBetween('created_at', [$start, $end]),
            $payment
        );

        $salesRevenue  = (clone $saleBase)->sum('total_amount');
        $salesDiscount = (clone $saleBase)->sum('discount_amount');
        $salesGST      = (clone $saleBase)->sum('tax_amount');
        $saleIds       = (clone $saleBase)->pluck('id');

        $cogs = $saleIds->isEmpty() ? 0 : DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereIn('sale_items.sale_id', $saleIds)
            ->selectRaw('SUM(sale_items.quantity * products.purchase_price) as cogs')
            ->value('cogs') ?? 0;

        $grossProfit   = $salesRevenue - $cogs;
        $totalExpenses = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $netProfit     = $grossProfit - $totalExpenses;

        // View uses $expenseBreakdown with ->category_name and ->total
        $expenseBreakdown = Expense::with('expenseCategory')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->get()
            ->map(fn($e) => (object)[
                'category_name' => $e->expenseCategory?->name ?? 'Uncategorized',
                'total'         => (float) $e->total,
            ]);

        // View checks isset($report) and uses $report['revenue'], ['cogs'], etc.
        $report = [
            'revenue'      => $salesRevenue,
            'discount'     => $salesDiscount,
            'gst'          => $salesGST,
            'cogs'         => $cogs,
            'gross_profit' => $grossProfit,
            'expenses'     => $totalExpenses,
            'net_profit'   => $netProfit,
        ];

        return view('reports.profit-loss', compact('start', 'end', 'payment', 'report', 'expenseBreakdown'));
    }

    /* ── GST ─────────────────────────────────────────────────── */
    public function gst(Request $request)
    {
        abort_unless(Module::isActive('report_gst'), 403);
        $month = $request->month ? Carbon::parse($request->month . '-01') : now()->startOfMonth();
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // View accesses as arrays: $row['tax_percent'], $row['taxable_amount'], etc.
        $salesTax = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw('products.tax_percent, SUM(sale_items.total_price) as taxable_amount, SUM(sale_items.total_price * products.tax_percent / 100) as tax_collected')
            ->groupBy('products.tax_percent')
            ->orderBy('products.tax_percent')
            ->get()
            ->map(fn($r) => [
                'tax_percent'    => $r->tax_percent,
                'taxable_amount' => (float) $r->taxable_amount,
                'tax_collected'  => (float) $r->tax_collected,
            ])->toArray();

        $purchaseTax = DB::table('grn_items')
            ->join('grns', 'grn_items.grn_id', '=', 'grns.id')
            ->whereBetween('grns.received_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('grn_items.tax_percent, SUM(grn_items.total_price) as taxable_amount, SUM(grn_items.total_price * grn_items.tax_percent / 100) as tax_paid')
            ->groupBy('grn_items.tax_percent')
            ->get()
            ->map(fn($r) => [
                'tax_percent'    => $r->tax_percent,
                'taxable_amount' => (float) $r->taxable_amount,
                'tax_paid'       => (float) $r->tax_paid,
            ])->toArray();

        // View checks isset($gstData)
        $gstData = [
            'sales_tax'    => $salesTax,
            'purchase_tax' => $purchaseTax,
        ];

        return view('reports.gst', compact('month', 'gstData'));
    }

    /* ── Cashier-Wise ────────────────────────────────────────── */
    public function cashierWise(Request $request)
    {
        abort_unless(Module::isActive('report_cashier'), 403);
        $date    = $request->date ? Carbon::parse($request->date) : today();
        $payment = $request->payment_method;

        $cashierData = $this->paymentFilter(
            Sale::with('user')->whereDate('created_at', $date)->where('status', 'completed'),
            $payment
        )
            ->selectRaw('user_id, COUNT(*) as bills_count, SUM(total_amount) as revenue, SUM(discount_amount) as total_discount, SUM(tax_amount) as total_gst')
            ->groupBy('user_id')
            ->get()
            ->map(fn($row) => (object)[
                'cashier_name'   => $row->user?->name ?? 'Unknown',
                'role'           => $row->user?->role_label ?? '',
                'bills_count'    => (int) $row->bills_count,
                'revenue'        => (float) $row->revenue,
                'total_discount' => (float) ($row->total_discount ?? 0),
                'total_gst'      => (float) ($row->total_gst ?? 0),
            ]);

        return view('reports.cashier-wise', compact('date', 'payment', 'cashierData'));
    }

    /* ── Stock Movement ──────────────────────────────────────── */
    public function stockMovement(Request $request)
    {
        abort_unless(Module::isActive('report_stock_movement'), 403);
        // View form sends from_date / to_date (not start/end)
        $start  = $request->from_date ? Carbon::parse($request->from_date) : today()->subDays(30);
        $end    = $request->to_date   ? Carbon::parse($request->to_date)->endOfDay() : today()->endOfDay();
        $search = $request->search;

        // View uses $stockData with: product_name, sku, category_name, opening_stock, stock_in, stock_out
        $query = DB::table('stock_movements')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('stock_movements.created_at', [$start, $end])
            ->where('products.is_active', true)
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.sku as sku,
                categories.name as category_name,
                MIN(stock_movements.stock_before) as opening_stock,
                SUM(CASE WHEN stock_movements.type = "in" THEN stock_movements.quantity ELSE 0 END) as stock_in,
                SUM(CASE WHEN stock_movements.type IN ("out","adjustment") THEN stock_movements.quantity ELSE 0 END) as stock_out
            ')
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name');

        if ($search) {
            $query->where(fn($q) => $q->where('products.name', 'like', '%'.$search.'%')
                ->orWhere('products.sku', 'like', '%'.$search.'%'));
        }

        $stockData = $query->orderBy('products.name')->get();

        return view('reports.stock-movement', compact('start', 'end', 'stockData'));
    }
}

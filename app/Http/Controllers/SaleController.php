<?php

namespace App\Http\Controllers;

use App\Helpers\Module;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Module::isActive('sales_history') && auth()->user()->can('view sales'), 403);
        $query = Sale::with(['customer', 'user']);
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%')
                ->orWhereHas('customer', fn($q) => $q->where('mobile', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%'));
        }
        $sales = $query->latest()->paginate(20)->withQueryString();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
            'customer_name'      => 'nullable|string|max:100',
            'customer_mobile'    => 'nullable|string|max:15',
            'discount_amount'    => 'nullable|numeric|min:0',
            'paid_amount'        => 'required|numeric|min:0',
            'payment_method'     => 'required|in:cash,upi,card,other',
            'notes'              => 'nullable|string',
        ]);

        $sale = DB::transaction(function () use ($validated) {
            $customer = null;
            if (!empty($validated['customer_mobile'])) {
                $customer = Customer::firstOrCreate(
                    ['mobile' => $validated['customer_mobile']],
                    ['name' => $validated['customer_name'] ?? 'Walk-in Customer']
                );
                if (!empty($validated['customer_name'])) {
                    $customer->update(['name' => $validated['customer_name']]);
                }
            }

            $subtotal    = 0;
            $totalTax    = 0;
            $saleItemsData = [];
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception($product->name . ' ka stock kam hai! Available: ' . $product->stock_quantity);
                }
                $itemDiscount  = $item['discount'] ?? 0;
                $itemBaseTotal = ($item['unit_price'] * $item['quantity']) - $itemDiscount;
                $taxPercent    = (float) $product->tax_percent;
                $itemTax       = round($itemBaseTotal * $taxPercent / 100, 2);
                $itemTotal     = $itemBaseTotal + $itemTax;
                $subtotal     += $itemBaseTotal;
                $totalTax     += $itemTax;
                $saleItemsData[] = [
                    'product'     => $product,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'discount'    => $itemDiscount,
                    'tax_percent' => $taxPercent,
                    'tax_amount'  => $itemTax,
                    'total_price' => $itemTotal,
                ];
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            $totalAmount    = $subtotal + $totalTax - $discountAmount;
            $paidAmount = $validated['paid_amount'];
            $changeAmount = max(0, $paidAmount - $totalAmount);

            $sale = Sale::create([
                'invoice_number'  => Sale::generateInvoiceNumber(),
                'customer_id'     => $customer?->id,
                'user_id'         => auth()->id(),
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $totalTax,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => $changeAmount,
                'payment_method'  => $validated['payment_method'],
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($saleItemsData as $itemData) {
                $product = $itemData['product'];
                $stockBefore = $product->stock_quantity;
                $product->decrement('stock_quantity', $itemData['quantity']);

                SaleItem::create([
                    'sale_id'         => $sale->id,
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'product_barcode' => $product->barcode,
                    'product_size'    => $product->size,
                    'product_color'   => $product->color,
                    'quantity'        => $itemData['quantity'],
                    'unit_price'      => $itemData['unit_price'],
                    'discount'        => $itemData['discount'],
                    'tax_percent'     => $itemData['tax_percent'],
                    'tax_amount'      => $itemData['tax_amount'],
                    'total_price'     => $itemData['total_price'],
                ]);

                StockMovement::create([
                    'product_id'   => $product->id,
                    'user_id'      => auth()->id(),
                    'type'         => 'out',
                    'quantity'     => $itemData['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after'  => $product->stock_quantity,
                    'reference'    => $sale->invoice_number,
                    'notes'        => 'Sale: ' . $sale->invoice_number,
                ]);
            }

            if ($customer) {
                $customer->increment('total_purchase', $totalAmount);
                $customer->increment('visit_count');
            }

            return $sale;
        });

        return response()->json([
            'success'     => true,
            'sale_id'     => $sale->id,
            'invoice'     => $sale->invoice_number,
            'print_url'   => route('sales.receipt', $sale),
        ]);
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items', 'user']);
        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['customer', 'items', 'user']);
        $settings = Setting::all()->pluck('value', 'key');
        return view('sales.receipt', compact('sale', 'settings'));
    }
}

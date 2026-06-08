<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = SaleReturn::with(['sale', 'user'])
            ->when($request->date, fn($q) => $q->whereDate('return_date', $request->date))
            ->latest()->paginate(20)->withQueryString();
        return view('sale-returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $sale = $request->sale_id ? Sale::with('items.product', 'customer')->find($request->sale_id) : null;
        return view('sale-returns.create', compact('sale'));
    }

    public function findSale(Request $request)
    {
        $sale = Sale::with('items.product', 'customer')
            ->where('invoice_number', $request->invoice_number)
            ->first();
        if (!$sale) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }
        if ($sale->saleReturn) {
            return response()->json(['error' => 'This invoice has already been returned (Return #' . $sale->saleReturn->return_number . ')'], 422);
        }
        return response()->json($sale->load('items.product', 'customer'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id'       => 'required|exists:sales,id',
            'return_date'   => 'required|date',
            'return_type'   => 'required|in:refund,exchange',
            'refund_method' => 'required|in:cash,upi,store_credit',
            'return_amount' => 'required|numeric|min:0.01',
            'reason'        => 'nullable|string|max:500',
            'items'         => 'required|array|min:1',
            'items.*.sale_item_id' => 'required',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $return = SaleReturn::create([
                'return_number' => SaleReturn::generateReturnNumber(),
                'sale_id'       => $request->sale_id,
                'user_id'       => auth()->id(),
                'return_date'   => $request->return_date,
                'return_amount' => $request->return_amount,
                'return_type'   => $request->return_type,
                'refund_method' => $request->refund_method,
                'reason'        => $request->reason,
            ]);

            // Return stock
            foreach ($request->items as $item) {
                $product     = \App\Models\Product::find($item['product_id']);
                $stockBefore = $product->stock_quantity;
                $product->increment('stock_quantity', $item['quantity']);
                StockMovement::create([
                    'product_id'   => $product->id,
                    'user_id'      => auth()->id(),
                    'type'         => 'in',
                    'quantity'     => $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockBefore + $item['quantity'],
                    'reference'    => $return->return_number,
                    'notes'        => 'Sale Return: ' . $return->return_number,
                ]);
            }

            // Update sale status
            Sale::find($request->sale_id)->update(['status' => 'refunded']);
        });

        return redirect()->route('sale-returns.index')->with('success', 'Sale return processed!');
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.items.product', 'sale.customer', 'user']);
        return view('sale-returns.show', compact('saleReturn'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\Module;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Module::isActive('stock_movements'), 403);
        $query = StockMovement::with(['product', 'user']);
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }
        $movements = $query->latest()->paginate(25)->withQueryString();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('stock.index', compact('movements', 'products'));
    }

    public function addStock()
    {
        abort_unless(Module::isActive('add_stock'), 403);
        return view('stock.add');
    }

    public function processAddStock(Request $request)
    {
        abort_unless(Module::isActive('add_stock'), 403);
        $validated = $request->validate([
            'barcode'        => 'required|string',
            'quantity'       => 'required|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
        ]);

        $product = Product::where('barcode', $validated['barcode'])->where('is_active', true)->first();
        if (!$product) {
            return back()->with('error', 'Product not found with barcode: ' . $validated['barcode']);
        }

        $canSeeCost = auth()->user()->can('view purchase price');

        DB::transaction(function () use ($product, $validated, $canSeeCost) {
            $stockBefore = $product->stock_quantity;
            $product->increment('stock_quantity', $validated['quantity']);
            if ($canSeeCost && !empty($validated['purchase_price'])) {
                $product->update(['purchase_price' => $validated['purchase_price']]);
            }
            StockMovement::create([
                'product_id'     => $product->id,
                'user_id'        => auth()->id(),
                'type'           => 'in',
                'quantity'       => $validated['quantity'],
                'stock_before'   => $stockBefore,
                'stock_after'    => $product->stock_quantity,
                'purchase_price' => $canSeeCost ? ($validated['purchase_price'] ?? $product->purchase_price) : null,
                'notes'          => $validated['notes'] ?? 'Stock added via barcode',
            ]);
        });

        return back()->with('success', 'Stock updated! Added ' . $validated['quantity'] . ' unit(s) for ' . $product->name . '.');
    }

    public function getProductByBarcode(Request $request)
    {
        $product = Product::with('category')
            ->where('barcode', $request->barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json([
            'id'             => $product->id,
            'name'           => $product->name,
            'barcode'        => $product->barcode,
            'sku'            => $product->sku,
            'size'           => $product->size,
            'color'          => $product->color,
            'stock_quantity' => $product->stock_quantity,
            'purchase_price' => $product->purchase_price,
            'selling_price'  => $product->selling_price,
        ]);
    }
}

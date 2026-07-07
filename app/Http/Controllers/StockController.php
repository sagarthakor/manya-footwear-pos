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
            'product_id'     => 'required|integer|exists:products,id',
            'quantity'       => 'required|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
        ]);

        $product = Product::where('id', $validated['product_id'])->where('is_active', true)->first();
        if (!$product) {
            return back()->with('error', 'Product not found.');
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

    public function adjustStock()
    {
        abort_unless(auth()->user()->can('adjust stock'), 403);
        return view('stock.adjust');
    }

    public function processAdjustStock(Request $request)
    {
        abort_unless(auth()->user()->can('adjust stock'), 403);
        $validated = $request->validate([
            'product_id'   => 'required|integer|exists:products,id',
            'new_quantity' => 'required|integer|min:0',
            'notes'        => 'required|string|max:500',
        ]);

        $product = Product::where('id', $validated['product_id'])->where('is_active', true)->first();
        if (!$product) {
            return back()->with('error', 'Product not found.');
        }

        $stockBefore = $product->stock_quantity;
        $diff = $validated['new_quantity'] - $stockBefore;

        if ($diff === 0) {
            return back()->with('error', 'New quantity is same as current stock. No adjustment made.');
        }

        DB::transaction(function () use ($product, $validated, $stockBefore, $diff) {
            $product->update(['stock_quantity' => $validated['new_quantity']]);
            StockMovement::create([
                'product_id'   => $product->id,
                'user_id'      => auth()->id(),
                'type'         => 'adjustment',
                'quantity'     => $diff,
                'stock_before' => $stockBefore,
                'stock_after'  => $validated['new_quantity'],
                'notes'        => $validated['notes'],
            ]);
        });

        $sign = $diff > 0 ? '+' . $diff : (string) $diff;
        return back()->with('success', "Stock adjusted for {$product->name}: {$sign} (now {$validated['new_quantity']} units).");
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

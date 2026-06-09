<?php

namespace App\Http\Controllers;

use App\Helpers\Module;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(fn($req, $next) => Module::isActive('products') ? $next($req) : abort(403));
    }

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('item_code', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->stock === 'low') {
            $query->whereColumn('stock_quantity', '<=', 'alert_quantity');
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::where('is_active', true)->get();
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();
        return view('products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $canSeeCost = auth()->user()->can('view purchase price');

        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:200',
            'sku'            => 'required|string|max:50|unique:products',
            'item_code'      => 'nullable|string|max:100|unique:products',
            'barcode'        => 'nullable|string|max:50|unique:products',
            'brand_id'       => 'nullable|exists:brands,id',
            'size'           => 'nullable|string|max:20',
            'color'          => 'nullable|string|max:50',
            'purchase_price' => $canSeeCost ? 'required|numeric|min:0' : 'sometimes|nullable|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'mrp'            => 'nullable|numeric|min:0',
            'tax_percent'    => 'nullable|numeric|in:0,5,12,18,28',
            'alert_quantity' => 'required|integer|min:0',
            'description'    => 'nullable|string',
        ]);

        if (!$canSeeCost) {
            $validated['purchase_price'] = 0;
        }

        $manualBarcode = !empty($validated['barcode']);

        if (!$manualBarcode) {
            $validated['barcode'] = $this->generateBarcode();
        }

        $validated['brand'] = $validated['brand_id']
            ? Brand::find($validated['brand_id'])?->name
            : null;

        $product = Product::create($validated);

        if ($manualBarcode) {
            return redirect()->route('products.index')
                ->with('success', 'Product added successfully.');
        }

        return redirect()->route('products.barcode', $product)
            ->with('success', 'Product added! Print the barcode label and stick it on the product.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'stockMovements' => fn($q) => $q->with('user')->latest()->limit(20)]);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $canSeeCost = auth()->user()->can('view purchase price');

        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:200',
            'sku'            => 'required|string|max:50|unique:products,sku,' . $product->id,
            'item_code'      => 'nullable|string|max:100|unique:products,item_code,' . $product->id,
            'barcode'        => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'brand_id'       => 'nullable|exists:brands,id',
            'size'           => 'nullable|string|max:20',
            'color'          => 'nullable|string|max:50',
            'purchase_price' => $canSeeCost ? 'required|numeric|min:0' : 'sometimes|nullable|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'mrp'            => 'nullable|numeric|min:0',
            'tax_percent'    => 'nullable|numeric|in:0,5,12,18,28',
            'alert_quantity' => 'required|integer|min:0',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        if (!$canSeeCost) {
            unset($validated['purchase_price']); // keep existing price untouched
        }
        $validated['brand'] = $validated['brand_id']
            ? Brand::find($validated['brand_id'])?->name
            : null;
        $product->update($validated);
        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->count() > 0) {
            return back()->with('error', 'This product has existing sales and cannot be deleted.');
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    public function barcodeLabel(Product $product)
    {
        return view('products.barcode', compact('product'));
    }

    public function getByBarcode(Request $request)
    {
        $product = Product::with('category')
            ->where('barcode', $request->barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function searchForPos(Request $request)
    {
        $q = trim($request->q ?? '');

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $products = Product::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('barcode', $q)
                      ->orWhere('sku', $q)
                      ->orWhere('sku', 'like', '%' . $q . '%')
                      ->orWhere('item_code', $q)
                      ->orWhere('item_code', 'like', '%' . $q . '%')
                      ->orWhere('name', 'like', '%' . $q . '%');
            })
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    private function generateBarcode(): string
    {
        do {
            $barcode = '8' . str_pad(random_int(0, 9999999999999), 12, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $barcode)->exists());
        return $barcode;
    }
}

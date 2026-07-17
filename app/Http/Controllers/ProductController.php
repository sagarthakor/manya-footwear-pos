<?php

namespace App\Http\Controllers;

use App\Helpers\Module;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    public function create(Request $request)
    {
        $categories = Category::where('is_active', true)->get();
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();
        $variantOf  = $request->variant_of ? Product::find($request->variant_of) : null;
        return view('products.create', compact('categories', 'brands', 'variantOf'));
    }

    public function store(Request $request)
    {
        $canSeeCost = auth()->user()->can('view purchase price');

        $validated = $request->validate([
            'category_id'           => 'required|exists:categories,id',
            'name'                  => 'required|string|max:200',
            'brand_id'              => 'nullable|exists:brands,id',
            'purchase_price'        => $canSeeCost ? 'required|numeric|min:0' : 'sometimes|nullable|numeric|min:0',
            'selling_price'         => 'required|numeric|min:0',
            'mrp'                   => 'nullable|numeric|min:0|gte:selling_price',
            'tax_percent'           => 'nullable|numeric|in:0,5,12,18,28',
            'alert_quantity'        => 'required|integer|min:0',
            'description'           => 'nullable|string',
            'variants'              => 'required|array|min:1',
            'variants.*.size'       => 'nullable|string|max:20',
            'variants.*.color'      => 'nullable|string|max:50',
            'variants.*.sku'        => 'nullable|string|max:50',
            'variants.*.item_code'  => 'nullable|string|max:100',
            'variants.*.barcode'    => 'nullable|string|max:50',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
        ], [
            'mrp.gte' => 'MRP cannot be less than the Selling Price.',
        ]);

        $this->validateVariantUniqueness($validated['variants']);
        $this->validateDuplicateNameSizeColor($validated['name'], $validated['variants']);

        if (!$canSeeCost) {
            $validated['purchase_price'] = 0;
        }

        $brandName = ($validated['brand_id'] ?? null)
            ? Brand::find($validated['brand_id'])?->name
            : null;

        DB::transaction(function () use ($validated, $brandName, $canSeeCost) {
            foreach ($validated['variants'] as $variant) {
                $skuProvided = trim((string) ($variant['sku'] ?? '')) !== '';
                $stockQuantity = (int) ($variant['stock_quantity'] ?? 0);

                $product = Product::create([
                    'category_id'    => $validated['category_id'],
                    'brand_id'       => $validated['brand_id'] ?? null,
                    'name'           => $validated['name'],
                    'sku'            => $skuProvided ? $variant['sku'] : null,
                    'item_code'      => $variant['item_code'] ?: null,
                    'barcode'        => $variant['barcode'] ?: null,
                    'brand'          => $brandName,
                    'size'           => $variant['size'] ?: null,
                    'color'          => $variant['color'] ?: null,
                    'purchase_price' => $validated['purchase_price'] ?? 0,
                    'selling_price'  => $validated['selling_price'],
                    'mrp'            => $validated['mrp'] ?? null,
                    'tax_percent'    => $validated['tax_percent'] ?? 0,
                    'stock_quantity' => $stockQuantity,
                    'alert_quantity' => $validated['alert_quantity'],
                    'description'    => $validated['description'] ?? null,
                ]);

                if (!$skuProvided) {
                    $product->update(['sku' => $this->generateSku($product->id)]);
                }

                if ($stockQuantity > 0) {
                    StockMovement::create([
                        'product_id'   => $product->id,
                        'user_id'      => auth()->id(),
                        'type'         => 'in',
                        'quantity'     => $stockQuantity,
                        'stock_before' => 0,
                        'stock_after'  => $stockQuantity,
                        'purchase_price' => $canSeeCost ? ($validated['purchase_price'] ?? 0) : null,
                        'notes'        => 'Initial stock at product creation',
                    ]);
                }
            }
        });

        $count = count($validated['variants']);

        return redirect()->route('products.index')
            ->with('success', $count > 1 ? "{$count} product variants added successfully." : 'Product added successfully.');
    }

    private function validateVariantUniqueness(array $variants, ?int $excludeProductId = null): void
    {
        $errors = [];
        $seen   = ['sku' => [], 'item_code' => [], 'barcode' => []];

        foreach ($variants as $i => $variant) {
            foreach (['sku', 'item_code', 'barcode'] as $field) {
                $value = trim((string) ($variant[$field] ?? ''));
                if ($value === '') {
                    continue;
                }
                if (isset($seen[$field][$value])) {
                    $errors["variants.{$i}.{$field}"] = ucfirst(str_replace('_', ' ', $field)) . " '{$value}' is duplicated in this form.";
                    continue;
                }
                $seen[$field][$value] = true;

                $query = Product::where($field, $value);
                if ($excludeProductId) {
                    $query->where('id', '!=', $excludeProductId);
                }
                if ($query->exists()) {
                    $errors["variants.{$i}.{$field}"] = ucfirst(str_replace('_', ' ', $field)) . " '{$value}' already exists.";
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function validateDuplicateNameSizeColor(string $name, array $variants, ?int $excludeProductId = null, bool $flatKey = false): void
    {
        $errors = [];
        $seen   = [];
        $normalizedName = strtolower(trim($name));

        foreach ($variants as $i => $variant) {
            $sizeKey  = strtolower(trim((string) ($variant['size'] ?? '')));
            $colorKey = strtolower(trim((string) ($variant['color'] ?? '')));
            $comboKey = $sizeKey . '|' . $colorKey;
            $errorKey = $flatKey ? 'size' : "variants.{$i}.size";

            if (isset($seen[$comboKey])) {
                $errors[$errorKey] = 'This Size/Color combination is duplicated in this form.';
                continue;
            }
            $seen[$comboKey] = true;

            $query = Product::whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
                ->whereRaw('LOWER(TRIM(COALESCE(size, ""))) = ?', [$sizeKey])
                ->whereRaw('LOWER(TRIM(COALESCE(color, ""))) = ?', [$colorKey]);
            if ($excludeProductId) {
                $query->where('id', '!=', $excludeProductId);
            }
            if ($query->exists()) {
                $errors[$errorKey] = "A product named '{$name}' with this Size/Color already exists.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function generateSku(int $productId): string
    {
        $base = 'SKU' . str_pad($productId, 6, '0', STR_PAD_LEFT);
        $sku  = $base;
        $i    = 1;
        while (Product::where('sku', $sku)->where('id', '!=', $productId)->exists()) {
            $sku = $base . '-' . $i++;
        }
        return $sku;
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
            'sku'            => 'nullable|string|max:50|unique:products,sku,' . $product->id,
            'item_code'      => 'nullable|string|max:100|unique:products,item_code,' . $product->id,
            'barcode'        => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'brand_id'       => 'nullable|exists:brands,id',
            'size'           => 'nullable|string|max:20',
            'color'          => 'nullable|string|max:50',
            'purchase_price' => $canSeeCost ? 'required|numeric|min:0' : 'sometimes|nullable|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'mrp'            => 'nullable|numeric|min:0|gte:selling_price',
            'tax_percent'    => 'nullable|numeric|in:0,5,12,18,28',
            'alert_quantity' => 'required|integer|min:0',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ], [
            'mrp.gte' => 'MRP cannot be less than the Selling Price.',
        ]);

        $this->validateDuplicateNameSizeColor(
            $validated['name'],
            [['size' => $validated['size'] ?? '', 'color' => $validated['color'] ?? '']],
            $product->id,
            flatKey: true
        );

        $validated['is_active'] = $request->boolean('is_active');
        $skuProvided = trim((string) ($validated['sku'] ?? '')) !== '';
        $validated['sku'] = $skuProvided ? $validated['sku'] : $this->generateSku($product->id);
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
        $barcodeImage = $product->barcode ? \App\Models\Barcode::toBase64Image($product->barcode) : null;

        return view('products.barcode', compact('product', 'barcodeImage'));
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

}

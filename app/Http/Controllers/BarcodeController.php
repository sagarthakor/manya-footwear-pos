<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Barcode::with('product');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('barcode', 'like', '%' . $request->search . '%');
        }

        $barcodes = $query->latest()->paginate(50)->withQueryString();

        $stats = [
            'unused'   => Barcode::where('status', 'unused')->count(),
            'assigned' => Barcode::where('status', 'assigned')->count(),
            'active'   => Barcode::where('status', 'active')->count(),
            'total'    => Barcode::count(),
        ];

        return view('barcodes.index', compact('barcodes', 'stats'));
    }

    public function bulkGenerateForm()
    {
        return view('barcodes.generate');
    }

    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:500',
            'prefix'   => 'nullable|string|max:6|regex:/^[A-Za-z0-9]+$/',
        ]);

        $prefix = strtoupper($request->prefix ?? 'MF');
        $count  = 0;

        for ($i = 0; $i < $request->quantity; $i++) {
            Barcode::create([
                'barcode' => $this->uniqueBarcode($prefix),
                'status'  => 'unused',
            ]);
            $count++;
        }

        return redirect()->route('barcodes.index')
            ->with('success', "{$count} barcodes generated successfully.");
    }

    public function assignForm(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'barcode']);
        $recent   = Barcode::with('product')->where('status', '!=', 'unused')
                        ->latest()->limit(10)->get();

        $prefill = $request->barcode ?? '';

        return view('barcodes.assign', compact('products', 'recent', 'prefill'));
    }

    public function assignBarcode(Request $request)
    {
        $request->validate([
            'barcode'    => 'required|string|max:50',
            'product_id' => 'required|exists:products,id',
        ]);

        $barcode = trim($request->barcode);

        $record = Barcode::firstOrCreate(
            ['barcode' => $barcode],
            ['status'  => 'unused']
        );

        if ($record->status === 'active') {
            return back()->withInput()
                ->with('error', "Barcode {$barcode} is already active (used in a sale).");
        }

        // Remove old product assignment if switching
        if ($record->product_id && $record->product_id != $request->product_id) {
            Product::where('id', $record->product_id)
                ->where('barcode', $barcode)
                ->update(['barcode' => null]);
        }

        $record->update([
            'product_id' => $request->product_id,
            'status'     => 'assigned',
        ]);

        Product::where('id', $request->product_id)->update(['barcode' => $barcode]);

        return back()->with('success', "Barcode {$barcode} assigned to product successfully.");
    }

    public function printLabels(Request $request)
    {
        $request->validate(['barcode_ids' => 'required|array']);
        $barcodes = Barcode::with('product')->whereIn('id', $request->barcode_ids)->get();

        $barcodeImages = [];
        foreach ($barcodes as $item) {
            $barcodeImages[$item->barcode] = Barcode::toBase64Image($item->barcode);
        }

        return view('barcodes.print-labels', compact('barcodes', 'barcodeImages'));
    }

    // Legacy: print labels from product_ids (used by products index page)
    public function generate(Request $request)
    {
        $request->validate(['product_ids' => 'required|array']);
        $products = Product::whereIn('id', $request->product_ids)->get();

        $barcodeImages = [];
        foreach ($products as $product) {
            if ($product->barcode) {
                $barcodeImages[$product->barcode] = Barcode::toBase64Image($product->barcode);
            }
        }

        return view('barcodes.print', compact('products', 'barcodeImages'));
    }

    public function image(string $barcode)
    {
        $generator = new BarcodeGeneratorPNG();
        $image = $generator->getBarcode($barcode, $generator::TYPE_CODE_128, 3, 60);
        return response($image)->header('Content-Type', 'image/png');
    }

    public function svg(string $barcode)
    {
        $generator = new BarcodeGeneratorSVG();
        $svg = $generator->getBarcode($barcode, $generator::TYPE_CODE_128, 2, 50);
        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    private function uniqueBarcode(string $prefix): string
    {
        do {
            $barcode = $prefix . date('Ymd') . str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Barcode::where('barcode', $barcode)->exists());

        return $barcode;
    }
}
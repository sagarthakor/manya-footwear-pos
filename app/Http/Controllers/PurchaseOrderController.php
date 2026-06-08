<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user']);
        if ($request->supplier_id) $query->where('supplier_id', $request->supplier_id);
        if ($request->status)      $query->where('status', $request->status);
        if ($request->date)        $query->whereDate('order_date', $request->date);
        $orders    = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        return view('purchase-orders.index', compact('orders', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'order_date'           => 'required|date',
            'expected_date'        => 'nullable|date|after_or_equal:order_date',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.tax_percent'  => 'nullable|numeric|min:0|max:100',
            'notes'                => 'nullable|string',
        ]);

        $po = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $taxTotal = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['unit_price'] * $item['quantity'];
                $taxAmt    = $lineTotal * (($item['tax_percent'] ?? 0) / 100);
                $subtotal += $lineTotal;
                $taxTotal += $taxAmt;
            }

            $po = PurchaseOrder::create([
                'po_number'       => PurchaseOrder::generatePoNumber(),
                'supplier_id'     => $request->supplier_id,
                'user_id'         => auth()->id(),
                'order_date'      => $request->order_date,
                'expected_date'   => $request->expected_date,
                'subtotal'        => $subtotal,
                'tax_amount'      => $taxTotal,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount'    => $subtotal + $taxTotal - ($request->discount_amount ?? 0),
                'status'          => 'ordered',
                'notes'           => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $lineTotal = $item['unit_price'] * $item['quantity'];
                $po->items()->create([
                    'product_id'       => $item['product_id'],
                    'ordered_quantity' => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'tax_percent'      => $item['tax_percent'] ?? 0,
                    'total_price'      => $lineTotal,
                ]);
            }

            return $po;
        });

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', 'Purchase Order ' . $po->po_number . ' created successfully!');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'items.product', 'grns']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'ordered'])) {
            return back()->with('error', 'Only draft or ordered POs can be edited.');
        }
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'products'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'ordered'])) {
            return back()->with('error', 'This PO cannot be edited.');
        }
        $request->validate([
            'status' => 'required|in:draft,ordered,cancelled',
            'notes'  => 'nullable|string',
        ]);
        $purchaseOrder->update(['status' => $request->status, 'notes' => $request->notes]);
        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order updated!');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->grns()->count() > 0) {
            return back()->with('error', 'This PO has GRNs linked and cannot be deleted.');
        }
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order deleted!');
    }
}

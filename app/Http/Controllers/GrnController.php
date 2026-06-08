<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrnController extends Controller
{
    public function index(Request $request)
    {
        $query = Grn::with(['supplier', 'user']);
        if ($request->supplier_id) $query->where('supplier_id', $request->supplier_id);
        if ($request->date)        $query->whereDate('received_date', $request->date);
        $grns      = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        return view('grns.index', compact('grns', 'suppliers'));
    }

    public function create(Request $request)
    {
        $suppliers     = Supplier::where('is_active', true)->orderBy('name')->get();
        $products      = Product::where('is_active', true)->with('category')->orderBy('name')->get();
        $purchaseOrder = $request->po_id ? PurchaseOrder::with('items.product')->find($request->po_id) : null;
        return view('grns.create', compact('suppliers', 'products', 'purchaseOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'         => 'required|exists:suppliers,id',
            'received_date'       => 'required|date',
            'invoice_number'      => 'nullable|string|max:100',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'paid_amount'         => 'nullable|numeric|min:0',
        ]);

        $grn = DB::transaction(function () use ($request) {
            $subtotal = 0; $taxTotal = 0;
            foreach ($request->items as $item) {
                $line      = $item['unit_price'] * $item['quantity'];
                $subtotal += $line;
                $taxTotal += $line * (($item['tax_percent'] ?? 0) / 100);
            }
            $total     = $subtotal + $taxTotal;
            $paid      = min((float)($request->paid_amount ?? 0), $total);
            $payStatus = $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $grn = Grn::create([
                'grn_number'        => Grn::generateGrnNumber(),
                'purchase_order_id' => $request->purchase_order_id ?? null,
                'supplier_id'       => $request->supplier_id,
                'user_id'           => auth()->id(),
                'received_date'     => $request->received_date,
                'subtotal'          => $subtotal,
                'tax_amount'        => $taxTotal,
                'total_amount'      => $total,
                'paid_amount'       => $paid,
                'payment_status'    => $payStatus,
                'invoice_number'    => $request->invoice_number,
                'notes'             => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $grn->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'tax_percent' => $item['tax_percent'] ?? 0,
                    'total_price' => $item['unit_price'] * $item['quantity'],
                ]);
                $product     = Product::find($item['product_id']);
                $stockBefore = $product->stock_quantity;
                $product->increment('stock_quantity', $item['quantity']);
                $product->update(['purchase_price' => $item['unit_price']]);
                StockMovement::create([
                    'product_id'     => $product->id,
                    'user_id'        => auth()->id(),
                    'type'           => 'in',
                    'quantity'       => $item['quantity'],
                    'stock_before'   => $stockBefore,
                    'stock_after'    => $stockBefore + $item['quantity'],
                    'purchase_price' => $item['unit_price'],
                    'reference'      => $grn->grn_number,
                    'notes'          => 'GRN: ' . $grn->grn_number,
                ]);
            }

            Supplier::find($request->supplier_id)->increment('total_payable', $total);
            if ($paid > 0) Supplier::find($request->supplier_id)->increment('total_paid', $paid);

            return $grn;
        });

        return redirect()->route('grns.show', $grn)
            ->with('success', 'GRN ' . $grn->grn_number . ' created. Stock updated!');
    }

    public function show(Grn $grn)
    {
        $grn->load(['supplier', 'user', 'items.product', 'purchaseOrder']);
        return view('grns.show', compact('grn'));
    }

    public function edit(Grn $grn)  { return back()->with('error', 'GRNs cannot be edited.'); }
    public function update(Request $request, Grn $grn) { return back(); }
    public function destroy(Grn $grn) { return back()->with('error', 'GRNs cannot be deleted.'); }
}

<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers with optional name/phone search, paginated 20 per page.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:15',
            'email'           => 'nullable|email|unique:suppliers,email',
            'gst_number'      => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $openingBalance = $validated['opening_balance'] ?? 0;

        Supplier::create(array_merge($validated, [
            'opening_balance' => $openingBalance,
            'total_payable'   => $openingBalance,
        ]));

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier with recent purchase orders and GRNs.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchaseOrders' => fn ($q) => $q->latest()->limit(10),
            'grns'           => fn ($q) => $q->latest()->limit(10),
        ]);

        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:15',
            'email'           => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'gst_number'      => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier from storage.
     * Prevents deletion if the supplier has purchase orders or GRNs.
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->count() > 0 || $supplier->grns()->count() > 0) {
            return back()->with('error', 'Cannot delete supplier with existing purchase orders or GRNs.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}

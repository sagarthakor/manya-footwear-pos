<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('mobile', 'like', '%' . $request->search . '%');
        }
        $customers = $query->latest()->paginate(20)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => fn($q) => $q->with('items')->latest()->limit(20)]);
        return view('customers.show', compact('customer'));
    }

    public function search(Request $request)
    {
        $customers = Customer::where('mobile', 'like', '%' . $request->q . '%')
            ->orWhere('name', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get(['id', 'name', 'mobile', 'total_purchase', 'visit_count']);
        return response()->json($customers);
    }
}

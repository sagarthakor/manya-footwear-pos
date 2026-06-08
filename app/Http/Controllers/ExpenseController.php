<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses with date/category/payment_method filters, paginated 20 per page.
     * Also passes today's total and current month's total.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['expenseCategory', 'user']);

        if ($request->filled('date')) {
            $query->whereDate('expense_date', $request->date);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->paginate(20)->withQueryString();

        $todayTotal = Expense::whereDate('expense_date', Carbon::today())->sum('amount');
        $monthTotal = Expense::whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->sum('amount');

        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'todayTotal', 'monthTotal', 'expenseCategories'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.create', compact('expenseCategories'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'amount'              => 'required|numeric|min:0.01',
            'expense_date'        => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_method'      => 'nullable|in:cash,upi,card,other',
            'notes'               => 'nullable|string',
        ]);

        Expense::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense)
    {
        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'expenseCategories'));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'amount'              => 'required|numeric|min:0.01',
            'expense_date'        => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_method'      => 'nullable|in:cash,upi,card,other',
            'notes'               => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}

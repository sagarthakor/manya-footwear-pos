@extends('layouts.app')
@section('title','Expenses')
@section('page-title','Expense Management')
@section('content')
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)"><div class="stat-value">&#8377;{{ number_format($todayTotal,0) }}</div><div style="opacity:.85">Today's Expenses</div></div></div>
  <div class="col-md-3"><div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#e67e22)"><div class="stat-value">&#8377;{{ number_format($monthTotal,0) }}</div><div style="opacity:.85">This Month</div></div></div>
</div>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center py-3">
    <span><i class="bi bi-cash-coin me-2"></i>Expenses</span>
    <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-danger"><i class="bi bi-plus"></i> Add Expense</a>
  </div>
  <div class="card-body border-bottom">
    <form method="GET" class="row g-2">
      <div class="col-md-2"><input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}"></div>
      <div class="col-md-3"><select name="category_id" class="form-select form-select-sm"><option value="">All Categories</option>@foreach($expenseCategories as $c)<option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
      <div class="col-md-2"><select name="payment_method" class="form-select form-select-sm"><option value="">All Methods</option><option value="cash" {{ request('payment_method')=='cash'?'selected':'' }}>Cash</option><option value="upi" {{ request('payment_method')=='upi'?'selected':'' }}>UPI</option><option value="card" {{ request('payment_method')=='card'?'selected':'' }}>Card</option></select></div>
      <div class="col-md-2"><button class="btn btn-sm btn-outline-primary">Filter</button> <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a></div>
    </form>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Date</th><th>Category</th><th>Title</th><th>Amount</th><th>Payment</th><th>By</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($expenses as $exp)
        <tr>
          <td>{{ $exp->expense_date->format('d M Y') }}</td>
          <td><span class="badge bg-secondary"><i class="bi {{ $exp->expenseCategory->icon }} me-1"></i>{{ $exp->expenseCategory->name }}</span></td>
          <td>{{ $exp->title }}</td>
          <td class="fw-bold">&#8377;{{ number_format($exp->amount,2) }}</td>
          <td><span class="badge bg-{{ $exp->payment_method==='cash'?'success':'info' }}">{{ strtoupper($exp->payment_method) }}</span></td>
          <td class="small">{{ $exp->user->name }}</td>
          <td>
            <a href="{{ route('expenses.edit',$exp) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="{{ route('expenses.destroy',$exp) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted py-4">No expenses found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($expenses->hasPages())<div class="card-footer">{{ $expenses->links() }}</div>@endif
</div>
@endsection

@extends('layouts.app')
@section('title','Edit Expense')
@section('page-title','Edit Expense')
@section('content')
<div class="row justify-content-center"><div class="col-md-6">
<div class="card">
  <div class="card-header py-3">Edit Expense</div>
  <div class="card-body">
    <form action="{{ route('expenses.update',$expense) }}" method="POST">
      @csrf @method('PUT')
      <div class="mb-3"><label class="form-label fw-semibold">Category *</label>
        <select name="expense_category_id" class="form-select" required>
          @foreach($expenseCategories as $c)<option value="{{ $c->id }}" {{ old('expense_category_id',$expense->expense_category_id)==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
        </select></div>
      <div class="mb-3"><label class="form-label fw-semibold">Title *</label><input type="text" name="title" class="form-control" value="{{ old('title',$expense->title) }}" required></div>
      <div class="mb-3"><label class="form-label fw-semibold">Amount (&#8377;) *</label><input type="number" name="amount" class="form-control" value="{{ old('amount',$expense->amount) }}" min="0.01" step="0.01" required></div>
      <div class="mb-3"><label class="form-label fw-semibold">Date *</label><input type="date" name="expense_date" class="form-control" value="{{ old('expense_date',$expense->expense_date->format('Y-m-d')) }}" required></div>
      <div class="mb-3"><label class="form-label fw-semibold">Payment Method</label>
        <select name="payment_method" class="form-select">
          <option value="cash" {{ old('payment_method',$expense->payment_method)=='cash'?'selected':'' }}>Cash</option>
          <option value="upi" {{ old('payment_method',$expense->payment_method)=='upi'?'selected':'' }}>UPI</option>
          <option value="card" {{ old('payment_method',$expense->payment_method)=='card'?'selected':'' }}>Card</option>
          <option value="other" {{ old('payment_method',$expense->payment_method)=='other'?'selected':'' }}>Other</option>
        </select></div>
      <div class="mb-4"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes',$expense->notes) }}</textarea></div>
      <div class="d-flex gap-2"><button type="submit" class="btn btn-danger">Update</button><a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
    </form>
  </div>
</div>
</div></div>
@endsection

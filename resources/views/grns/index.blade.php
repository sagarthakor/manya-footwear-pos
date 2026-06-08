@extends('layouts.app')
@section('title','GRN - Goods Received')
@section('page-title','Goods Received Notes (GRN)')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center py-3">
    <span><i class="bi bi-truck me-2"></i>GRN List</span>
    <a href="{{ route('grns.create') }}" class="btn btn-sm btn-danger"><i class="bi bi-plus"></i> New GRN</a>
  </div>
  <div class="card-body border-bottom">
    <form method="GET" class="row g-2">
      <div class="col-md-3"><select name="supplier_id" class="form-select form-select-sm"><option value="">All Suppliers</option>@foreach($suppliers as $s)<option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
      <div class="col-md-2"><input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}"></div>
      <div class="col-md-2"><button class="btn btn-sm btn-outline-primary">Filter</button> <a href="{{ route('grns.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a></div>
    </form>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>GRN No</th><th>Supplier</th><th>Date</th><th>Invoice No</th><th>Total</th><th>Paid</th><th>Payment</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($grns as $grn)
        <tr>
          <td class="fw-semibold">{{ $grn->grn_number }}</td>
          <td>{{ $grn->supplier->name }}</td>
          <td>{{ $grn->received_date->format('d M Y') }}</td>
          <td class="text-muted">{{ $grn->invoice_number ?? '-' }}</td>
          <td>&#8377;{{ number_format($grn->total_amount,0) }}</td>
          <td>&#8377;{{ number_format($grn->paid_amount,0) }}</td>
          <td><span class="badge bg-{{ $grn->payment_status==='paid'?'success':($grn->payment_status==='partial'?'warning':'danger') }}">{{ ucfirst($grn->payment_status) }}</span></td>
          <td><a href="{{ route('grns.show',$grn) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a></td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted py-4">No GRNs found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($grns->hasPages())<div class="card-footer">{{ $grns->links() }}</div>@endif
</div>
@endsection

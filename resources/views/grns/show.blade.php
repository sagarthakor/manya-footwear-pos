@extends('layouts.app')
@section('title',$grn->grn_number)
@section('page-title','GRN: ' . $grn->grn_number)
@section('content')
<div class="row g-3">
<div class="col-md-8">
<div class="card">
  <div class="card-header py-3"><i class="bi bi-truck me-2"></i>Items Received</div>
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Tax%</th><th class="text-end">Total</th></tr></thead>
      <tbody>
        @foreach($grn->items as $item)
        <tr>
          <td>{{ $item->product->name }}<div class="text-muted small">{{ $item->product->sku }}</div></td>
          <td>{{ $item->quantity }}</td>
          <td>&#8377;{{ number_format($item->unit_price,2) }}</td>
          <td>{{ $item->tax_percent }}%</td>
          <td class="text-end fw-bold">&#8377;{{ number_format($item->total_price,2) }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot class="table-light">
        <tr><td colspan="4" class="text-end">Subtotal:</td><td class="text-end">&#8377;{{ number_format($grn->subtotal,2) }}</td></tr>
        <tr><td colspan="4" class="text-end">Tax:</td><td class="text-end">&#8377;{{ number_format($grn->tax_amount,2) }}</td></tr>
        <tr class="fw-bold"><td colspan="4" class="text-end">Total:</td><td class="text-end text-danger">&#8377;{{ number_format($grn->total_amount,2) }}</td></tr>
      </tfoot>
    </table>
  </div>
</div>
</div>
<div class="col-md-4">
<div class="card">
  <div class="card-header py-3">GRN Details</div>
  <div class="card-body">
    <table class="table table-sm table-borderless mb-0">
      <tr><td class="text-muted">GRN No:</td><td class="fw-bold">{{ $grn->grn_number }}</td></tr>
      <tr><td class="text-muted">Supplier:</td><td>{{ $grn->supplier->name }}</td></tr>
      <tr><td class="text-muted">Received:</td><td>{{ $grn->received_date->format('d M Y') }}</td></tr>
      @if($grn->invoice_number)<tr><td class="text-muted">Invoice No:</td><td>{{ $grn->invoice_number }}</td></tr>@endif
      @if($grn->purchaseOrder)<tr><td class="text-muted">PO:</td><td><a href="{{ route('purchase-orders.show',$grn->purchaseOrder) }}">{{ $grn->purchaseOrder->po_number }}</a></td></tr>@endif
      <tr><td class="text-muted">Total:</td><td class="fw-bold text-danger">&#8377;{{ number_format($grn->total_amount,2) }}</td></tr>
      <tr><td class="text-muted">Paid:</td><td class="text-success">&#8377;{{ number_format($grn->paid_amount,2) }}</td></tr>
      <tr><td class="text-muted">Outstanding:</td><td class="{{ ($grn->total_amount-$grn->paid_amount)>0?'text-danger':'' }}">&#8377;{{ number_format($grn->total_amount-$grn->paid_amount,2) }}</td></tr>
      <tr><td class="text-muted">Payment:</td><td><span class="badge bg-{{ $grn->payment_status==='paid'?'success':($grn->payment_status==='partial'?'warning':'danger') }}">{{ ucfirst($grn->payment_status) }}</span></td></tr>
      <tr><td class="text-muted">Received By:</td><td>{{ $grn->user->name }}</td></tr>
    </table>
  </div>
</div>
</div>
</div>
@endsection

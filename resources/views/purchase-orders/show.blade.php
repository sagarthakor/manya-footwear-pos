@extends('layouts.app')
@section('title',$purchaseOrder->po_number)
@section('page-title','Purchase Order: ' . $purchaseOrder->po_number)
@section('content')
<div class="row g-3">
<div class="col-md-8">
<div class="card">
  <div class="card-header d-flex justify-content-between py-3">
    <span><i class="bi bi-file-earmark-text me-2"></i>{{ $purchaseOrder->po_number }}</span>
    @if(in_array($purchaseOrder->status,['ordered','partially_received']))
    <a href="{{ route('grns.create',['po_id'=>$purchaseOrder->id]) }}" class="btn btn-sm btn-success"><i class="bi bi-truck me-1"></i>Receive Stock (GRN)</a>
    @endif
  </div>
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead class="table-light"><tr><th>Product</th><th>Ordered</th><th>Received</th><th>Unit Price</th><th>Tax%</th><th class="text-end">Total</th></tr></thead>
      <tbody>
        @foreach($purchaseOrder->items as $item)
        <tr>
          <td>{{ $item->product->name }}<div class="text-muted small">{{ $item->product->sku }}</div></td>
          <td>{{ $item->ordered_quantity }}</td>
          <td>{{ $item->received_quantity }}</td>
          <td>&#8377;{{ number_format($item->unit_price,2) }}</td>
          <td>{{ $item->tax_percent }}%</td>
          <td class="text-end fw-bold">&#8377;{{ number_format($item->total_price,2) }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot class="table-light">
        <tr><td colspan="5" class="text-end">Subtotal:</td><td class="text-end">&#8377;{{ number_format($purchaseOrder->subtotal,2) }}</td></tr>
        <tr><td colspan="5" class="text-end">Tax:</td><td class="text-end">&#8377;{{ number_format($purchaseOrder->tax_amount,2) }}</td></tr>
        <tr class="fw-bold"><td colspan="5" class="text-end">Total:</td><td class="text-end text-danger">&#8377;{{ number_format($purchaseOrder->total_amount,2) }}</td></tr>
      </tfoot>
    </table>
  </div>
</div>
@if($purchaseOrder->grns->count())
<div class="card mt-3">
  <div class="card-header py-3">Linked GRNs</div>
  <div class="card-body p-0">
    <table class="table mb-0"><thead class="table-light"><tr><th>GRN No</th><th>Date</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr></thead>
    <tbody>@foreach($purchaseOrder->grns as $grn)
    <tr><td>{{ $grn->grn_number }}</td><td>{{ $grn->received_date->format('d M Y') }}</td><td>&#8377;{{ number_format($grn->total_amount,0) }}</td><td>&#8377;{{ number_format($grn->paid_amount,0) }}</td><td><span class="badge bg-{{ $grn->payment_status==='paid'?'success':($grn->payment_status==='partial'?'warning':'danger') }}">{{ ucfirst($grn->payment_status) }}</span></td><td><a href="{{ route('grns.show',$grn) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a></td></tr>
    @endforeach</tbody></table>
  </div>
</div>
@endif
</div>
<div class="col-md-4">
<div class="card">
  <div class="card-header py-3">PO Details</div>
  <div class="card-body">
    <table class="table table-sm table-borderless mb-0">
      <tr><td class="text-muted">Supplier:</td><td class="fw-semibold">{{ $purchaseOrder->supplier->name }}</td></tr>
      <tr><td class="text-muted">Order Date:</td><td>{{ $purchaseOrder->order_date->format('d M Y') }}</td></tr>
      @if($purchaseOrder->expected_date)<tr><td class="text-muted">Expected:</td><td>{{ $purchaseOrder->expected_date->format('d M Y') }}</td></tr>@endif
      <tr><td class="text-muted">Status:</td><td><span class="badge bg-primary">{{ ucfirst(str_replace('_',' ',$purchaseOrder->status)) }}</span></td></tr>
      <tr><td class="text-muted">Created By:</td><td>{{ $purchaseOrder->user->name }}</td></tr>
      @if($purchaseOrder->notes)<tr><td class="text-muted">Notes:</td><td>{{ $purchaseOrder->notes }}</td></tr>@endif
    </table>
  </div>
</div>
</div>
</div>
@endsection

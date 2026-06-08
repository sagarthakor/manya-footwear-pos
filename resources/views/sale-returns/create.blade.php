@extends('layouts.app')
@section('title','Process Sale Return')
@section('page-title','Process Sale Return')
@section('content')
<div class="row">
<div class="col-md-5">
<div class="card mb-3">
  <div class="card-header py-3"><i class="bi bi-search me-2"></i>Find Invoice</div>
  <div class="card-body">
    <div class="input-group">
      <input type="text" id="invoiceSearch" class="form-control" placeholder="Enter invoice number...">
      <button class="btn btn-danger" onclick="findInvoice()"><i class="bi bi-search"></i> Find</button>
    </div>
    <div id="searchError" class="text-danger small mt-2" style="display:none"></div>
  </div>
</div>
<div id="saleDetails" style="display:none">
<form action="{{ route('sale-returns.store') }}" method="POST" id="returnForm">
@csrf
<input type="hidden" name="sale_id" id="saleIdInput">
<div class="card mb-3">
  <div class="card-header py-3">Sale Items</div>
  <div class="card-body p-0">
    <table class="table mb-0" id="saleItemsTable">
      <thead class="table-light"><tr><th>Return?</th><th>Product</th><th>Orig Qty</th><th>Return Qty</th></tr></thead>
      <tbody id="saleItemsBody"></tbody>
    </table>
  </div>
</div>
<div class="card">
  <div class="card-header py-3">Return Details</div>
  <div class="card-body">
    <div class="mb-2"><label class="form-label fw-semibold">Return Date *</label><input type="date" name="return_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required></div>
    <div class="mb-2"><label class="form-label fw-semibold">Return Type *</label>
      <select name="return_type" class="form-select" required><option value="refund">Refund (Money Back)</option><option value="exchange">Exchange</option></select></div>
    <div class="mb-2"><label class="form-label fw-semibold">Refund Method *</label>
      <select name="refund_method" class="form-select" required><option value="cash">Cash</option><option value="upi">UPI</option><option value="store_credit">Store Credit</option></select></div>
    <div class="mb-2"><label class="form-label fw-semibold">Return Amount (&#8377;) *</label>
      <input type="number" name="return_amount" id="returnAmount" class="form-control" min="0.01" step="0.01" required></div>
    <div class="mb-3"><label class="form-label fw-semibold">Reason</label><textarea name="reason" class="form-control" rows="2"></textarea></div>
    <button type="submit" class="btn btn-danger w-100"><i class="bi bi-check-circle me-2"></i>Process Return</button>
  </div>
</div>
</form>
</div>
</div>
<div class="col-md-7">
<div class="card" id="saleInfoCard" style="display:none">
  <div class="card-header py-3">Sale Information</div>
  <div class="card-body" id="saleInfo"></div>
</div>
</div>
</div>
@endsection
@push('scripts')
<script>
function findInvoice(){
  var inv=document.getElementById('invoiceSearch').value.trim();
  if(!inv)return;
  fetch('/api/sale-returns/find?invoice_number='+encodeURIComponent(inv))
    .then(function(r){if(!r.ok)throw new Error('Invoice not found');return r.json();})
    .then(function(sale){
      document.getElementById('searchError').style.display='none';
      document.getElementById('saleDetails').style.display='block';
      document.getElementById('saleInfoCard').style.display='block';
      document.getElementById('saleIdInput').value=sale.id;
      document.getElementById('returnAmount').value=sale.total_amount;

      var info='<table class="table table-sm table-borderless mb-0">';
      info+='<tr><td class="text-muted">Invoice:</td><td class="fw-bold">'+sale.invoice_number+'</td></tr>';
      info+='<tr><td class="text-muted">Customer:</td><td>'+(sale.customer?sale.customer.name:'Walk-in')+'</td></tr>';
      info+='<tr><td class="text-muted">Total:</td><td class="fw-bold text-danger">&#8377;'+parseFloat(sale.total_amount).toFixed(2)+'</td></tr>';
      info+='</table>';
      document.getElementById('saleInfo').innerHTML=info;

      var tbody=document.getElementById('saleItemsBody');tbody.innerHTML='';
      sale.items.forEach(function(item,i){
        tbody.innerHTML+='<tr><td><input type="checkbox" name="items['+i+'][return]" class="form-check-input item-check" data-idx="'+i+'" checked></td>'+
          '<td>'+item.product_name+'<div class="text-muted small">'+(item.product_size?'Sz:'+item.product_size:'')+(item.product_color?' '+item.product_color:'')+'</div></td>'+
          '<td>'+item.quantity+'</td>'+
          '<td><input type="number" name="items['+i+'][quantity]" class="form-control form-control-sm return-qty" value="'+item.quantity+'" min="1" max="'+item.quantity+'" style="width:70px">'+
          '<input type="hidden" name="items['+i+'][sale_item_id]" value="'+item.id+'">'+
          '<input type="hidden" name="items['+i+'][product_id]" value="'+item.product_id+'"></td></tr>';
      });
    })
    .catch(function(e){
      document.getElementById('searchError').textContent=e.message;
      document.getElementById('searchError').style.display='block';
      document.getElementById('saleDetails').style.display='none';
    });
}
document.getElementById('invoiceSearch').addEventListener('keydown',function(e){if(e.key==='Enter')findInvoice();});
</script>
@endpush
